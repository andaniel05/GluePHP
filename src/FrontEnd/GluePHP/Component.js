
///////////////////////
// GluePHP.Component //
///////////////////////

(function(GluePHP) {
'use strict';

function Component(id, app, model = {}, element) {

    GluePHP.BaseEntity.call(this);

    this.id = id;
    this.app = app;
    this.model = Object.seal(model);
    this.element = element;
    this.childrenClass = 'gphp-' + id + '-children';
    this.childrenElement = element instanceof Element ?
        element.querySelector('.gphp-children') : null;
};

Component.prototype = Object.create(GluePHP.BaseEntity.prototype);
Component.prototype.constructor = Component;

Component.prototype.dispatchInApp = function(eventName, event) {
    var eventNameInApp = this.id + '.' + eventName;
    this.app.dispatch(eventNameInApp, event);
};

Component.prototype.dispatch = function(eventName, event) {

    this.dispatchInLocal(eventName, event);

    if ( ! event.propagationStopped) {
        this.dispatchInApp(eventName, event);
    }
};

GluePHP.Component = Component;

})(window.GluePHP = window.GluePHP || {});
