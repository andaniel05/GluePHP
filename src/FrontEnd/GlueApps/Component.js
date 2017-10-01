
////////////////////////
// GlueApps.Component //
////////////////////////

(function(GlueApps) {
'use strict';

function Component(id, app, model = {}, html) {

	GlueApps.BaseEntity.call(this);

	this.id    = id;
	this.app   = app;
	this.model = Object.seal(model);
	this.html  = html;
};

Component.prototype = Object.create(GlueApps.BaseEntity.prototype);
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

GlueApps.Component = Component;

})(window.GlueApps = window.GlueApps || {});
