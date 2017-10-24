
////////////////////////////////
// GluePHP.ComponentContainer //
////////////////////////////////

(function(GluePHP) {
'use strict';

function ComponentContainer() {
    this.components = {};
};

ComponentContainer.prototype.getAllComponents = function() {
    return this.components;
};

ComponentContainer.prototype._findOne = function(components, id) {

    for (var componentId in components) {
        var component = components[componentId];
        if (id == component.id) {
            return component;
        } else {
            component = this._findOne(component.components, id);
            if (component) {
                return component;
            }
        }
    }

    return null;
};

ComponentContainer.prototype.getComponent = function(id) {

    var idList = id.split(' ');

    if (idList.length == 1) {
        return this._findOne(this.components, id);
    } else {

        var hash = {};
        for (var componentId of idList) {
            hash[componentId] = null;
        }

        var container = this;
        for (var componentId of idList) {
            var component = container.getComponent(componentId);
            if (component) {
                hash[componentId] = component;
                container = component;
            } else {
                break;
            }
        }

        return hash[idList.pop()];
    }
};

ComponentContainer.prototype.addComponent = function(component) {
    this.components[component.id] = component;
};

ComponentContainer.prototype.existsComponent = function(id) {
    return this.components.hasOwnProperty(id);
};

ComponentContainer.prototype.dropComponent = function(id) {

    var component = this.components[id];
    delete this.components[id];

    if (component instanceof GluePHP.Component &&
        component.element instanceof Element)
    {
        component.element.remove();
    }
};

GluePHP.ComponentContainer = ComponentContainer;

})(window.GluePHP = window.GluePHP || {});
