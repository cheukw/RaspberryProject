/* 
 * File:   ActionFactory.cpp
 * Author: Ismael
 * 
 * Created on 13 de marzo de 2014, 04:26 PM
 */

#include <string>

#include "ActionFactory.h"

ActionFactory::ActionFactory() {
}

ActionFactory::ActionFactory(const ActionFactory& orig) {
}

ActionFactory::~ActionFactory() {
}

Action* ActionFactory::createFromNotification(Notification notification, ConnectionSSL* connection, std::vector<std::string> rejected_actions_list){
    return NULL;
}
