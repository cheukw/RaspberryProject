
/* 
 * File:   ConnectionSSL.cpp
 * Author: iavalenzu
 * 
 * Created on 30 de junio de 2013, 05:31 PM
 */


#include "ConnectionSSL.h"


//#include "ActionFactory.h"
//#include "IncomingActionExecutor.h"

ConnectionSSL::ConnectionSSL(int _connection_fd, struct event_base* _evbase, SSL_CTX* _ssl_ctx) {

    this->last_activity = time(NULL);
    this->created = time(NULL);

    this->evbase = _evbase;
    this->fd = _connection_fd;
    this->ctx = _ssl_ctx;

    this->ssl = SSL_new(_ssl_ctx);

    this->device = new Device();

    this->bev = bufferevent_openssl_socket_new(this->evbase, this->fd, this->ssl, BUFFEREVENT_SSL_ACCEPTING, BEV_OPT_CLOSE_ON_FREE);

    bufferevent_enable(this->bev, EV_READ);
    bufferevent_setcb(this->bev, this->ssl_readcb, NULL, NULL, (void *) this);




    const char *fifo = "/tmp/client.fifo";
    int res;

    unlink(fifo);

    if (access(fifo, F_OK) == -1) {
        res = mkfifo(fifo, 0666);
        if (res != 0) {
            printf("Could not create fifo %s\n", fifo);
            exit(EXIT_FAILURE);
        }
    }

    int notifier = open(fifo, O_RDONLY | O_NONBLOCK, 0);


    struct bufferevent *bev_notifier;

    bev_notifier = bufferevent_new(notifier, this->notifier_cb, NULL, NULL, (void *) this);

    bufferevent_base_set(this->evbase, bev_notifier);

    bufferevent_enable(bev_notifier, EV_READ);





}

void ConnectionSSL::notifier_cb(struct bufferevent *bev, void *arg) {

    printf("ConnectionSSL::notifier_cb\n");
    
    ConnectionSSL *connection_ssl;

    connection_ssl = (ConnectionSSL *) arg;    

    struct evbuffer *in = bufferevent_get_input(bev);


    bufferevent_write_buffer(connection_ssl->bev, in);




}

void ConnectionSSL::ssl_readcb(struct bufferevent * bev, void * arg) {

    struct evbuffer *in = bufferevent_get_input(bev);

    printf("Received %zu bytes\n", evbuffer_get_length(in));
    printf("----- data ----\n");
    printf("%.*s\n", (int) evbuffer_get_length(in), evbuffer_pullup(in, -1));

    bufferevent_write_buffer(bev, in);

}

ConnectionSSL::~ConnectionSSL() {
}

void ConnectionSSL::closeConnection() {

    /*
     * Desconectamos el dispositivo
     */

    //this->device->disconnect();

    /*
     * Cerramos la coneccion SSL y liberamos recursos    
     */
    close(this->fd);
    SSL_shutdown(this->ssl);
    SSL_free(this->ssl); /* release SSL state */
    SSL_CTX_free(this->ctx);
    free(this->device);
}

/*
int ConnectionSSL::writeNotification(Notification notification) {
    return RaspiUtils::writeJSON(this->ssl, notification.getJSON());
}

Notification ConnectionSSL::readNotification() {
    return Notification(RaspiUtils::readJSON(this->ssl));
}

Device* ConnectionSSL::getDevice() {
    return this->device;
}
 */
int ConnectionSSL::canReadNotification() {
    return this->can_read_notification;
}

void ConnectionSSL::setLastActivity() {
    this->last_activity = time(NULL);
}

SSL* ConnectionSSL::getSSL() {
    return this->ssl;
}

/*
void ConnectionSSL::processAction() {

    IncomingActionExecutor incoming_executor(this);

    
      //Leemos la notificacion entrante y ejecutamos la accion asociada
     
  

    incoming_executor.readAndWriteResponse();

}
 */

void ConnectionSSL::manageCloseConnection(int sig) {

    std::cout << getpid() << " > Cerrando Cliente!!" << std::endl;

    this->closeConnection();

    //Matamos el proceso
    exit(sig);

}
/*
void ConnectionSSL::manageInactiveConnection(int sig) {

    
     // Se procede a manejar si la coneccion esta inactiva, por lo tanto se impide una lectura de notificacion
     

    this->can_read_notification = false;

    
     // Si el proceso ha estado inactivo por mas de MAX_INACTIVE_TIME, terminamos su ejecucion.
     //Si el proceso ha estado vivo mas de MAX_ALIVE_TIME, terminanos su ejecucion
     

    int now = time(NULL);

    int inactive_lapse = now - this->last_activity;
    int alive_lapse = now - this->created;

    if (inactive_lapse >= MAX_INACTIVE_TIME || alive_lapse >= MAX_ALIVE_TIME) {

        cout << getpid() << " > Timeout process!!" << endl;

        this->manageCloseConnection(sig);

    } else {
        cout << getpid() << " > Shutdown in ";
        cout << "[ Inactive: " << RaspiUtils::humanTime(MAX_INACTIVE_TIME - inactive_lapse) << " ] ";
        cout << "[ Alive: " << RaspiUtils::humanTime(MAX_ALIVE_TIME - alive_lapse) << " ]" << endl;
    }

    // Reset the timer so we get called again in CHECK_INACTIVE_INTERVAL seconds
    alarm(CHECK_INACTIVE_INTERVAL);

}


/*
void ConnectionSSL::manageNotificationWaiting(int sig) {

    this->can_read_notification = true;

}
 */

