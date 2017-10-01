
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
