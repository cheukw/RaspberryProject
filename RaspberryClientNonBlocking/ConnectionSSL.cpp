
/* 
 * File:   ConnectionSSL.cpp
 * Author: iavalenzu
 * 
 * Created on 30 de junio de 2013, 05:31 PM
 */


#include "ConnectionSSL.h"


//#include "ActionFactory.h"

ConnectionSSL::ConnectionSSL(SSL_CTX* _ctx, struct event_base* _evbase) {

    this->ctx = _ctx;
    this->evbase = _evbase;
    this->ssl = NULL;
    this->fd = -1;

}

void ConnectionSSL::createEncryptedSocket() {

    this->ssl = SSL_new(this->ctx);

    /* 
     * Perform the connection 
     */

    this->bev = bufferevent_openssl_socket_new(this->evbase, -1, this->ssl,
            BUFFEREVENT_SSL_CONNECTING,
            BEV_OPT_DEFER_CALLBACKS | BEV_OPT_CLOSE_ON_FREE);


    struct hostent *host;
    struct sockaddr_in addr;

    if ((host = gethostbyname(HOST_NAME.c_str())) == NULL) {
        perror("gethostbyname");
        abort();
    }

    bzero(&addr, sizeof (addr));
    addr.sin_family = AF_INET;
    addr.sin_port = htons(PORT_NUM);
    addr.sin_addr.s_addr = *(long*) (host->h_addr);

    this->fd = bufferevent_socket_connect(this->bev, (struct sockaddr*) &addr, sizeof (addr));

    if (this->fd < 0) {
        bufferevent_free(this->bev);
        return;
    }


    int flags = fcntl(this->fd, F_GETFL);
    if (flags >= 0) {
        flags |= O_NONBLOCK;
        if (fcntl(this->fd, F_SETFL, flags) < 0)
            perror("fcntl");

    }


    bufferevent_enable(this->bev, EV_READ | EV_WRITE);
    bufferevent_setcb(this->bev, ConnectionSSL::ssl_readcb, ConnectionSSL::ssl_writecb, ConnectionSSL::ssl_eventcb, (void *) this);




    struct bufferevent *bev_stdin;

    bev_stdin = bufferevent_new(0, ConnectionSSL::standard_input_cb, NULL, NULL, (void *) this);

    bufferevent_base_set(this->evbase, bev_stdin);

    bufferevent_enable(bev_stdin, EV_READ);


    struct event *ev;
    ev = event_new(this->evbase, -1, EV_PERSIST, ConnectionSSL::periodic_cb, (void *) this);
    struct timeval ten_sec = {10, 0};
    event_add(ev, &ten_sec);


}

void ConnectionSSL::periodic_cb(evutil_socket_t fd, short what, void *arg) {

}

void ConnectionSSL::standard_input_cb(struct bufferevent *bev, void *arg) {
/*
    printf("ConnectionSSL::standard_input_cb\n");

    ConnectionSSL *connection_ssl;
    connection_ssl = static_cast<ConnectionSSL*> (arg);


    struct evbuffer *in = bufferevent_get_input(bev);

    bufferevent_write_buffer(connection_ssl->bev, in);
*/
}

void ConnectionSSL::ssl_readcb(struct bufferevent * bev, void * arg) {

    std::cout << "Reading from SSL connection..." << std::endl;

    char buf[1024];
    int n;
    
    struct evbuffer *input = bufferevent_get_input(bev);
    
    //std::cout <<  evbuffer_pullup(input, -1) << std::endl;

    while ((n = evbuffer_remove(input, buf, sizeof(buf))) > 0) {
        std::cout << buf << std::endl;
    }    
   
}

void ConnectionSSL::ssl_writecb(struct bufferevent * bev, void * arg) {

    std::cout << "Writing on SSL connection..." << std::endl;

}

void ConnectionSSL::ssl_eventcb(struct bufferevent *bev, short events, void *arg) {

    if (events & BEV_EVENT_CONNECTED) {

        /*
         * Luego de connectarnos. enviamos una notificacion de autentificacion
         */

        Notification authentication;
        authentication.setAction("ACTION_AUTHENTICATE");
        authentication.clearData();
        authentication.addDataItem(JSONNode("ACCESS_TOKEN", ACCESS_TOKEN));
        
        std::string notification_json = authentication.toString();
        
        bufferevent_write(bev, notification_json.c_str(), notification_json.size());

        printf("BEV_EVENT_CONNECTED\n");
        
        return;
        
    } else if (events & BEV_EVENT_EOF) {
        printf("Disconnected from the remote host\n");
    } else if (events & BEV_EVENT_ERROR) {
        
        
        printf("Network error\n");
    } else if (events & BEV_EVENT_TIMEOUT) {
        printf("Timeout\n");
    }

}

ConnectionSSL::~ConnectionSSL() {
}

void ConnectionSSL::informClosingToServer() {
    /*
    Notification close_notification;
    close_notification.setAction("CLOSE_CONNECTION");
    
    this->writeNotification(close_notification);
     */
}

void ConnectionSSL::closeConnection() {

    /*
     * Cerramos la coneccion SSL y liberamos recursos    
     */
    close(this->fd);
    SSL_shutdown(this->ssl);
    SSL_free(this->ssl); /* release SSL state */
    SSL_CTX_free(this->ctx);

}

void ConnectionSSL::showCerts() {

    X509 *cert;
    char *line;

    cert = SSL_get_peer_certificate(this->ssl); // get the server's certificate 

    if (cert != NULL) {

        std::cout << getpid() << " > Server certificates" << std::endl;
        line = X509_NAME_oneline(X509_get_subject_name(cert), 0, 0);
        std::cout << getpid() << " > Subject: " << line << std::endl;
        free(line); // free the malloc'ed string 
        line = X509_NAME_oneline(X509_get_issuer_name(cert), 0, 0);
        std::cout << getpid() << " > Issuer: " << line << std::endl;
        free(line); // free the malloc'ed string
        X509_free(cert); // free the malloc'ed certificate copy 

    } else {
        std::cout << getpid() << " > No certificates" << std::endl;
    }
}

/*
int ConnectionSSL::writeNotification(Notification notification) {
    return RaspiUtils::writeJSON(this->ssl, notification.getJSON());
}

Notification ConnectionSSL::readNotification() {
    return Notification(RaspiUtils::readJSON(this->ssl));
}
 */
SSL* ConnectionSSL::getSSL() {
    return this->ssl;
}

void ConnectionSSL::manageCloseConnection(int sig) {

    std::cout << getpid() << " > Cerrando Cliente!!" << std::endl;

    this->closeConnection();

    //Matamos el proceso
    exit(sig);

}
