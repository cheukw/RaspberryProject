/* 
 * File:   NotificationReader.cpp
 * Author: Ismael
 * 
 * Created on 15 de marzo de 2014, 09:27 AM
 */

#include "IncomingActionExecutor.h"
#include "ConnectionSSL.h"


IncomingActionExecutor::IncomingActionExecutor() : ActionExecutor() {
}

IncomingActionExecutor::~IncomingActionExecutor() {
}

void IncomingActionExecutor::execute(Notification notification, ConnectionSSL *connection){

    IncomingAction *action = IncomingActionFactory::createFromNotification(notification, connection, this->rejected_actions_list);
    
    action->toDo();
    
    this->incoming_action_list.push_back(action);
        
}

