/* 
 * File:   IncomingActionFactory.h
 * Author: Ismael
 *
 * Created on 15 de marzo de 2014, 09:44 AM
 */

#ifndef INCOMINGACTIONFACTORY_H
#define	INCOMINGACTIONFACTORY_H

#include "ActionFactory.h"
#include "IncomingAction.h"

#include "ActionGetFortune.h"


class IncomingActionFactory : public ActionFactory {
public:
    IncomingActionFactory();
    IncomingActionFactory(const IncomingActionFactory& orig);
    virtual ~IncomingActionFactory();
    static Action* createFromNotification(Notification notification, ConnectionSSL* connection);
private:

};

#endif	/* INCOMINGACTIONFACTORY_H */

