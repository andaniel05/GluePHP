/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

/////////////
// GluePHP //
/////////////

(function(GluePHP) {
'use strict';

Event.prototype.propagationStopped = false;
Event.prototype.stopEventPropagation = function() {
    this.propagationStopped = true;
};
Event.prototype.isPropagationStopped = function() {
    return this.propagationStopped;
};

GluePHP.Factory = {

    App: {

        createRequestEvent: function(request) {
            var event = new CustomEvent('app.request', {detail: request});
            event.request = request;
            return event;
        },

        createAppActionEvent: function(action) {
            var event = new CustomEvent('app.action', {detail: action});
            event.action = action;
            return event;
        },

        createResponseEvent: function(response) {
            var event = new CustomEvent('app.request', {detail: response});
            event.response = response;
            return event;
        },

        createFailedResponseEvent: function(request) {
            var event = new CustomEvent('app.failed_response', {detail: request});
            event.request = request;
            return event;
        },

        createResponseErrorEvent: function(response) {
            var event = new CustomEvent('app.response_error', {detail: response});
            event.response = response;
            return event;
        },
    },

    Component: {

        createRemoteUpdateEvent: function(updates) {
            var event = new CustomEvent('remote_update', {detail: updates});
            event.updates = updates;
            return event;
        },

        createEndRemoteUpdateEvent: function(data) {
            var event = new CustomEvent('end_remote_update', {detail: data});
            event.data = data;
            return event;
        },
    },
};

GluePHP.Helpers = {

    capitalizeFirstLetter: function(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    },

    getSetter: function(attribute) {
        return 'set' + GluePHP.Helpers.capitalizeFirstLetter(attribute);
    },
};

})(window.GluePHP = window.GluePHP || {});
