/* 
 * File:   NotificationReader.h
 * Author: Ismael
 *
 * Created on 15 de marzo de 2014, 09:27 AM
 */



#ifndef INCOMINGACTIONEXECUTOR_H
#define	INCOMINGACTIONEXECUTOR_H

#include "ActionExecutor.h"

#include "IncomingActionFactory.h"
#include "OutcomingActionFactory.h"


class IncomingActionExecutor : public ActionExecutor {
public:
    IncomingActionExecutor(ConnectionSSL* connection);
    virtual ~IncomingActionExecutor();
    
    void readAndWriteResponse();
    Notification read();
    
private:

};

#endif	/* INCOMINGACTIONEXECUTOR_H */

