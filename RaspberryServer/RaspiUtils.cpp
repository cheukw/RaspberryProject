/* 
 * File:   RaspiUtils.cpp
 * Author: iavalenzu
 * 
 * Created on 5 de julio de 2013, 11:13 AM
 */

#include "RaspiUtils.h"

RaspiUtils::RaspiUtils() {
}

RaspiUtils::~RaspiUtils() {
}

int RaspiUtils::writeJSON(SSL *ssl, JSONNode json) {

    std:string out;
    int bytes;
    int totalbytes = 0;
    int outlen;

    out = json.write_formatted();

    if(out.empty()){
        return 0;
    }

    outlen = out.size();

    while(true){
    
        bytes = SSL_write(ssl, out.data(), BUFSIZE); // encrypt & send message 

        if (bytes <= 0) {
            ERR_print_errors_fp(stderr);
            perror("SSL_write");
            abort();
        }
        
        out += bytes;
        totalbytes += bytes;
        
        if(totalbytes >= outlen) break;

    }
    
    return totalbytes;

}

JSONNode RaspiUtils::readJSON(SSL *ssl) {

    JSONNode tmpjson;
    
    int bytes;
    char buf[BUFSIZE];
    string msg = "";

    while(true){

        bytes = SSL_read(ssl, buf, sizeof(buf)); /* get reply & decrypt */

        if (bytes <= 0) {
            ERR_print_errors_fp(stderr);
            perror("SSL_read");
            abort();
        }
 
        buf[bytes] = 0;
        
        msg.append(buf);
        
        cout << "MSG: " << msg << endl;
        
        tmpjson = libjson::parse(msg);
        
        if(tmpjson.type() != JSON_NULL) break;
                
    }
    
    return tmpjson;

}

void RaspiUtils::writePid(const char *file_name) {

    FILE* fd;

    fd = fopen(file_name, "w");
    fprintf(fd, "%d", getpid());
    fclose(fd);
    
}