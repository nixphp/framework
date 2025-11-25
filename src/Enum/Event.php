<?php

namespace NixPHP\Enum;

enum Event: string implements EventInterface
{
    case CONTROLLER_CALLING = 'controller.calling'; // Before the controller is called
    case CONTROLLER_CALLED = 'controller.called';   // After the controller is called
    case ROUTE_MATCHING = 'route.matching';         // Route is about to match or not
    case ROUTE_MATCHED = 'route.matched';           // Route has matched
    case ROUTE_NOT_FOUND = 'route.not_found';       // Route not found
    case REQUEST_START = 'request.start';           // Before the app starts to handle the request
    case REQUEST_BODY = 'request.body';             // Before the body is parsed
    case REQUEST_END = 'request.end';               // Not in use yet
    case RESPONSE_SEND = 'response.send';           // Before the app starts to send a response
    case RESPONSE_BODY = 'response.body';           // Before the body is sent to the client
    case RESPONSE_HEADER = 'response.header';       // Before the headers are sent to the client
    case RESPONSE_END = 'response.end';             // Before the app shuts down
    case EXCEPTION = 'exception';                   // When somewhere in the app, an exception is thrown
}
