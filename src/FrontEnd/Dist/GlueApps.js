
//////////////
// GlueApps //
//////////////

(function(GlueApps) {
'use strict';

Event.prototype.propagationStopped = false;
Event.prototype.stopEventPropagation = function() {
	this.propagationStopped = true;
};
Event.prototype.isPropagationStopped = function() {
	return this.propagationStopped;
};

GlueApps.Factory = {

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

GlueApps.Helpers = {

	capitalizeFirstLetter: function(string) {
		return string.charAt(0).toUpperCase() + string.slice(1);
	},

	getSetter: function(attribute) {
		return 'set' + GlueApps.Helpers.capitalizeFirstLetter(attribute);
	},
};

})(window.GlueApps = window.GlueApps || {});


//////////////////////////////
// GlueApps.EventDispatcher //
//////////////////////////////

(function(GlueApps) {
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

GlueApps.EventDispatcher = EventDispatcher;

})(window.GlueApps = window.GlueApps || {});


/////////////////////////////////
// GlueApps.ComponentContainer //
/////////////////////////////////

(function(GlueApps) {
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
	delete this.components[id];
};

GlueApps.ComponentContainer = ComponentContainer;

})(window.GlueApps = window.GlueApps || {});


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


//////////////////
// GlueApps.App //
//////////////////

(function(GlueApps) {
'use strict';

function App(url, token) {

	GlueApps.BaseEntity.call(this);

	this.url = url;
	this.token = token;
	this.buffer = {};
	this.actionHandlers = {};
	this.processors = [];
	this.httpRequests = [];
	this.componentClasses = {};
	this.requestMethod = 'POST';
	this.requestKey = 'request';
	this.debug = false;
};

App.prototype = Object.create(GlueApps.BaseEntity.prototype);
App.prototype.constructor = App;

App.prototype.getStatus = function() {
	return null;
};

App.prototype.dispatchInRemote = function(name, event) {

	var app = this;
	if ( ! app.url) return;

	var request = this.buildRequest(name, event);
	var requestEvent = GlueApps.Factory.App.createRequestEvent(request);

	this.dispatchInLocal('app.request', requestEvent);

	var httpRequest = new XMLHttpRequest();
	httpRequest.request = request;
	httpRequest.streaming = false;

	var lastResponseLen = false;
	httpRequest.onprogress = function(event) {

		if ( ! event.currentTarget) return;
		httpRequest.streaming = true;

		var currentResponse;
		var responseBuffer = event.currentTarget.response;

		if (lastResponseLen === false) {
			currentResponse = responseBuffer;
			lastResponseLen = responseBuffer.length;
		} else {
			currentResponse = responseBuffer.substring(lastResponseLen);
			lastResponseLen = responseBuffer.length;
		}

		try {
			var message = JSON.parse(currentResponse);
		} catch (e) {
			console.log(e, currentResponse);
		}

		if ('code' in message) {

			var responseEvent = GlueApps.Factory.App.createResponseEvent(message);
			app.dispatchInLocal('app.response', responseEvent);

			var response = responseEvent.response;
			response.request = this.request;
			app.processResponse(response);

		} else {
			app.runAction(message);
		}
	};

	httpRequest.onreadystatechange = function() {

		if (httpRequest.readyState === XMLHttpRequest.DONE) {

			app.httpRequests.splice(
				app.httpRequests.indexOf(httpRequest), 1
			);

			if (httpRequest.status === 200 && httpRequest.streaming == false) {

				try {
					var response = JSON.parse(httpRequest.responseText);
				} catch (e) {
					console.log(e, httpRequest.responseText);
				}

				var responseEvent = GlueApps.Factory.App.createResponseEvent(response);
				app.dispatchInLocal('app.response', responseEvent);

				response = responseEvent.response;
				response.request = this.request;
				app.processResponse(response);

			} else {
				var failedResponseEvent = GlueApps.Factory.App.createFailedResponseEvent(request);
				app.dispatchInLocal('app.failed_response', failedResponseEvent);
			}
		}
	};

	httpRequest.open(this.requestMethod, this.url, true);
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	httpRequest.send(this.requestKey + '=' + JSON.stringify(requestEvent.request));
	app.httpRequests.push(httpRequest);

	this.buffer = {};

	for (var update of request.serverUpdates) {
		var component = this.getComponent(update.componentId);
		if (component) {
			var event = GlueApps.Factory.Component.createRemoteUpdateEvent(update.data);
			component.dispatchInLocal('remote_update', event);
		}
	}
};

App.prototype.dispatch = function(name, event) {

	this.dispatchInLocal(name, event);

	if ( ! event.propagationStopped) {
		this.dispatchInRemote(name, event);
	}
};

App.prototype.buildRequest = function(name, event) {

	var updates = [];
	for (var componentId in this.buffer) {
		var update = {
			componentId: componentId,
			data: this.buffer[componentId],
		};
		updates.push(update);
	}

	return {
		appToken: this.token,
		status: this.getStatus(),
		eventName: name,
		eventData: event,
		serverUpdates: updates,
	};
};

App.prototype.registerUpdate = function(componentId, attribute, value) {

	var component = this.getComponent(componentId);

	if ( ! (component instanceof GlueApps.Component)) {
		return;
	}

	if ( ! (component.model.hasOwnProperty(attribute))) {
		return;
	}

	if ( ! (this.buffer.hasOwnProperty(componentId))) {
		this.buffer[componentId] = {};
	}

	this.buffer[componentId][attribute] = value;
};

App.prototype.runAction = function(action) {

	if (true === this.debug) {
		console.log('Action:', action);
	}

	var event = GlueApps.Factory.App.createAppActionEvent(action);
	this.dispatchInLocal('app.action', event);

	return this.actionHandlers[action.handler](action.data, this);
};

App.prototype.processResponse = function(response) {

	var app = this;

	if (true === this.debug) {
		console.log('Response:', response);
	}

	if (response.code === 200) {

		response.request.serverUpdates.forEach(function(serverUpdate) {
			var component = app.getComponent(serverUpdate.componentId);
			component.dispatchInLocal(
				'end_remote_update',
				GlueApps.Factory.Component.createEndRemoteUpdateEvent(serverUpdate.data)
			)
		});

		for (var id in response.clientUpdates) {
			var clientUpdate = response.clientUpdates[id];
			var component = app.getComponent(clientUpdate.componentId);
			for (var attr in clientUpdate.data) {
				var setter = GlueApps.Helpers.getSetter(attr);
				component[setter](clientUpdate.data[attr], false);
			}
		}

		for (var id in response.actions) {
			app.runAction(response.actions[id]);
		}

	} else {
		var event = GlueApps.Factory.App.createResponseErrorEvent(response);
		app.dispatchInLocal('app.response_error', event);
	}
};

App.prototype.processComponent = function(component) {
	for (var processor of this.processors) {
		processor(component);
	}
};

GlueApps.App = App;

})(window.GlueApps = window.GlueApps || {});


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
