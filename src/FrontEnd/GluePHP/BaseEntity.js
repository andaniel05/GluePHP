/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

/////////////////////////
// GluePHP.BaseEntity  //
/////////////////////////

(function(GluePHP) {
'use strict';

function BaseEntity() {

    GluePHP.ComponentContainer.call(this);

    this.dispatcher = new GluePHP.EventDispatcher();
};

BaseEntity.prototype = Object.create(GluePHP.ComponentContainer.prototype);
BaseEntity.prototype.constructor = BaseEntity;

BaseEntity.prototype.addListener = function(name, listener) {
    this.dispatcher.addListener(name, listener);
};

BaseEntity.prototype.dispatchInLocal = function(name, event) {
    this.dispatcher.dispatch(name, event);
};

GluePHP.BaseEntity = BaseEntity;

})(window.GluePHP = window.GluePHP || {});
