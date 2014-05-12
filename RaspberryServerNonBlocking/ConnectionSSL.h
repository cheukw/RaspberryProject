/* 
 * File:   ConnectionSSL.h
 * Author: iavalenzu
 *
 * Created on 30 de junio de 2013, 05:31 PM
 */

#ifndef CONNECTIONSSL_H
#define	CONNECTIONSSL_H

#include <cstdlib>
#include <unistd.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <iostream>

#include <sys/stat.h> 
#include <fcntl.h>

#include <openssl/ssl.h>
#include <openssl/err.h>
#include <openssl/rand.h>

#include <event.h>
#include <event2/listener.h>
#include <event2/bufferevent_ssl.h>

#include "JSONBuffer.h"

#include "Notification.h"
#include "IncomingActionExecutor.h"

#include "DatabaseAdapter.h"
#include "Utilities.h"


using namespace std;

class ConnectionSSL {
public:
    ConnectionSSL(int _connection_fd, struct event_base *_evbase, SSL *_ssl);
    ~ConnectionSSL();
    

    void createAssociatedFifo();
    void createSecureBufferEvent(int _connection_fd, SSL *_ssl);

    static void readSSLCallback(struct bufferevent *bev, void *arg);
    static void eventSSLCallback(struct bufferevent *bev, short events, void *arg);
    static void writeSSLCallback(struct bufferevent *bev, void *arg);

    static void readFIFOCallback(struct bufferevent *bev, void *arg);
    static void eventFIFOCallback(struct bufferevent *bev, short events, void *arg);

    static void successJSONCallback(JSONNode &node, void *arg);
    static void errorJSONCallback(int code, void *arg);
    
    void close();

    
    int checkCredentialsOnDatabase();
    int disconnectFromDatabase();
    
    
    void clearCredentials();

    void setAccessToken(std::string _access_token);
    

private:

    struct event_base *evbase;

    struct bufferevent *ssl_bev;

    struct bufferevent *fifo_bev;
    
    JSONBuffer json_buffer;

    std::string access_token;
    std::string fifo_filename;

    std::string user_id;
    std::string user_token;

    
    int authenticated;
    std::string connection_id;
    
    
    IncomingActionExecutor incoming_action_executor;

    

};

#endif	/* CONNECTIONSSL_H */

