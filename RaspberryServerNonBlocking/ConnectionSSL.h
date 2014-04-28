/* 
 * File:   ConnectionSSL.h
 * Author: iavalenzu
 *
 * Created on 30 de junio de 2013, 05:31 PM
 */

#ifndef CONNECTIONSSL_H
#define	CONNECTIONSSL_H

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


//#include "Notification.h"

//#include "RaspiUtils.h"
#include "Device.h"


using namespace std;

class ConnectionSSL {
    
public:
    ConnectionSSL(int _connection_fd, struct event_base* _evbase, SSL* _ssl);
    
    static void ssl_readcb(struct bufferevent * bev, void * arg);
    static void ssl_eventcb(struct bufferevent *bev, short events, void *ptr);
    
    static void periodic_cb(evutil_socket_t fd, short what, void *arg);
    static void fifo_readcb(struct bufferevent * bev, void * arg);
    
    struct event_base* evbase;
    struct bufferevent* bev;
    
private:
    
    int fd;
    SSL* ssl;
        
    
};

#endif	/* CONNECTIONSSL_H */

