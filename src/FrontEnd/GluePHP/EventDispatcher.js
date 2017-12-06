/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

/////////////////////////////
// GluePHP.EventDispatcher //
/////////////////////////////

(function(GluePHP) {
'use strict';

function EventDispatcher() {
    this.events = {};
};

EventDispatcher.prototype.addListener = function(name, listener) {

    if ( ! this.events.hasOwnProperty(name)) {
        this.events[name] = [];
    }

    this.events[name].push(listener);
};

EventDispatcher.prototype.dispatch = function(name, event) {

    if (this.events.hasOwnProperty(name) && Array.isArray(this.events[name])) {
        for (var listener of this.events[name]) {
            if ( ! event.isPropagationStopped()) {
                listener(event);
            } else {
                break;
            }
        }
    }
};

GluePHP.EventDispatcher = EventDispatcher;

})(window.GluePHP = window.GluePHP || {});
