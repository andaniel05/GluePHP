
/////////////////////////
// GlueApps.BaseEntity //
/////////////////////////

(function(GlueApps) {
'use strict';

function BaseEntity() {

	GlueApps.ComponentContainer.call(this);

	this.dispatcher = new GlueApps.EventDispatcher();
};

BaseEntity.prototype = Object.create(GlueApps.ComponentContainer.prototype);
BaseEntity.prototype.constructor = BaseEntity;

BaseEntity.prototype.addListener = function(name, listener) {
	this.dispatcher.addListener(name, listener);
};

BaseEntity.prototype.dispatchInLocal = function(name, event) {
	this.dispatcher.dispatch(name, event);
};

GlueApps.BaseEntity = BaseEntity;

})(window.GlueApps = window.GlueApps || {});
