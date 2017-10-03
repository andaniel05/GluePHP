
function getDummyRequest() {
    return {
        appToken: 'token1',
        status: null,
        eventName: 'event1',
        eventData: {},
        serverUpdates: [],
    };
}

suite('GluePHP.App', function() {

    const App = GluePHP.App;
    const Component = GluePHP.Component;
    const EventDispatcher = GluePHP.EventDispatcher;

    test('is instance of GluePHP.BaseEntity', function() {
        var app = new App();
        assert.instanceOf(app, GluePHP.BaseEntity);
    });

    suite('#url', function() {

        ['url1', 'url2'].forEach(function(url) {
            test(`is ${url} when url argument is ${url}`, function() {
                var app = new App(url);
                assert.equal(app.url, url);
            });
        });
    });

    suite('#token', function() {

        ['token1', 'token2'].forEach(function(token) {
            test(`is ${token} when token argument is ${token}`, function() {
                var app = new App('localhost', token);
                assert.equal(app.token, token);
            });
        });
    });

    suite('#requestMethod', function() {

        test('is equal to "POST" by default', function() {
            var app = new App();
            assert.equal(app.requestMethod, "POST");
        });
    });

    suite('#requestKey', function() {

        test('is equal to "request" by default', function() {
            var app = new App();
            assert.equal(app.requestKey, "request");
        });
    });

    suite('#httpRequests', function() {

        setup(function() {
            this.server = sinon.fakeServer.create();
        });

        teardown(function() {
            this.server.restore();
        });

        test('is empty by default', function() {
            var app = new App();
            assert.deepEqual(app.httpRequests, []);
        });

        test('contains all the http requests in processing', function() {

            var url = 'http://localhost.com/controller.php';
            var app = new App(url);

            app.dispatchInRemote('event.name');
            var currentRequest = app.httpRequests[0];
            assert.instanceOf(currentRequest, XMLHttpRequest);
        });

        test('do not contains the http request when it is finished', function() {

            var url = 'http://localhost.com/controller.php';
            var app = new App(url);

            app.dispatchInRemote('event.name');
            this.server.respond();

            assert.deepEqual(app.httpRequests, []);
        });
    });

    suite('#getStatus()', function() {

        test('return null by default', function() {
            var app = new App();
            assert.isNull(app.getStatus());
        });
    });

    suite('#dispatchInRemote()', function() {

        setup(function() {
            this.server = sinon.fakeServer.create();
        });

        teardown(function() {
            this.server.restore();
        });

        ['http://localhost1.com', 'http://localhost2.com'].forEach(function(url) {
            test(`generate a ajax request to ${url} when app url is ${url}`, function() {

                var app = new App(url);

                app.dispatchInRemote('component.event');

                var request = this.server.requests[0];

                assert.isTrue(request.async);
                assert.equal(request.url, url);
            });
        });

        ['key1', 'key2'].forEach(function(key) {
            test(`the ajax request contains "${key}=..." when #requestKey is equal to "${key}". The app request data is encode with JSON.`, function() {

                var eventName = 'component.event';
                var event = new Event(eventName);
                var app = new App('http://localhost/controller.php');

                var dummyRequest = getDummyRequest();
                sinon.stub(app, 'buildRequest').returns(dummyRequest);

                app.requestKey = key;

                app.dispatchInRemote(eventName, event);
                var request = this.server.requests[0];

                assert.equal(request.requestBody, key + '=' + JSON.stringify(dummyRequest));
            });
        });

        test('the ajax request contains the app request object', function() {

            var eventName = 'component.event';
            var event = new Event(eventName);
            var app = new App('http://localhost/controller.php');

            var dummyRequest = getDummyRequest();
            sinon.stub(app, 'buildRequest').returns(dummyRequest);

            app.dispatchInRemote(eventName, event);
            var ajaxRequest = this.server.requests[0];

            assert.equal(ajaxRequest.request, dummyRequest);
        });

        test('the content type of ajax request is equal to "application/x-www-form-urlencoded"', function() {

            var eventName = 'component.event';
            var event = new Event(eventName);
            var app = new App('http://localhost/controller.php');

            app.dispatchInRemote(eventName, event);
            var request = this.server.requests[0];

            assert.include(request.requestHeaders['Content-Type'], 'application/x-www-form-urlencoded');
        });

        ['GET', 'POST'].forEach(function(method) {
            test(`when #requestMethod is "${method}" the ajax request is per "${method}"`, function() {

                var eventName = 'component.event';
                var event = new Event(eventName);
                var app = new App('http://localhost/controller.php');
                app.requestMethod = method;

                app.dispatchInRemote(eventName, event);
                var request = this.server.requests[0];

                assert.equal(request.method, method);
            });
        });

        test('clear the buffer', function() {

            var app = new App('http://localhost/controller.php');
            app.buffer = {
                component1: {},
            };

            app.dispatchInRemote('component.event');

            assert.isEmpty(app.buffer);
        });

        test('the local event "app.request" can change the request', function() {

            var app = new App('http://localhost/controller.php');
            var dummyRequest = getDummyRequest();

            app.addListener('app.request', function(event) {
                event.request = dummyRequest;
            });

            app.dispatchInRemote('component.event');
            var request = this.server.requests[0];

            assert.equal(request.requestBody, 'request=' + JSON.stringify(dummyRequest));
        });

        suite('dispatch "remote_update" in components', function() {

            setup(function() {

                this.app = new App('http://localhost/controller.php');
                this.model = {attr1: 1, attr2: 2};
                this.component1 = new Component('component1', this.app, this.model);
                this.component2 = new Component('component2', this.app, this.model);
                this.dispatchInLocal1 = sinon.spy(this.component1, 'dispatchInLocal');
                this.dispatchInLocal2 = sinon.spy(this.component2, 'dispatchInLocal');

                this.app.addComponent(this.component1);
                this.app.addComponent(this.component2);

                this.app.registerUpdate('component1', 'attr1', 11);
                this.app.registerUpdate('component1', 'attr2', 22);
            });

            test('the "remote_update" event is dispatched on components that are being updated', function() {

                this.app.dispatchInRemote('event.name');

                sinon.assert.calledOnce(this.dispatchInLocal1);
                sinon.assert.calledWith(this.dispatchInLocal1, 'remote_update');
                sinon.assert.notCalled(this.dispatchInLocal2);
            });

            test('the "remote_update" event contains all the update data', function() {

                var listener = function(event) {
                    assert.deepEqual(event.updates, {attr1: 11, attr2: 22});
                };

                var spy = sinon.spy(listener);

                this.component1.addListener('remote_update', spy);

                this.app.dispatchInRemote('event.name');

                sinon.assert.called(spy);
            });
        });

        test('dispatch the "app.failed_response" when response code of ajax request is not 200 ', function() {

            var url = 'http://localhost/controller.php';

            this.server.respondWith(
                'GET', url,
                [400, {'Content-Type': 'application/json'}, '[]']
            );

            var app = new App(url);

            var request = getDummyRequest();
            sinon.stub(app, 'buildRequest').returns(request);

            var listenerSpy = sinon.spy();
            app.addListener('app.failed_response', listenerSpy);

            app.dispatchInRemote('event.name');
            this.server.respond();

            sinon.assert.calledOnce(listenerSpy);
            sinon.assert.calledWithMatch(
                listenerSpy,
                sinon.match.instanceOf(Event)
                    .and(sinon.match.has('request', sinon.match(request)))
            );
        });

        test('invoke to #processResponse() with app request when ajax response is received', function() {

            var url = 'http://localhost.com/';
            var eventName = 'eventName';
            var event = new Event(eventName);
            var dummyRequest = getDummyRequest();

            this.server.respondWith(
                'POST', url,
                [200, {'Content-Type': 'application/json'}, '{"code": 200}']
            );

            var app = new App(url);
            var spy = sinon.spy(app, 'processResponse');
            sinon.stub(app, 'buildRequest').returns(dummyRequest);

            app.dispatchInRemote(eventName, event);
            this.server.respond();

            sinon.assert.calledOnce(spy);
            sinon.assert.calledWithMatch(spy,
                sinon.match.has('request', sinon.match.same(dummyRequest))
            );
        });

        suite('"app.response" event', function() {

            setup(function() {
                this.url = 'http://localhost/controller.php';
                this.app = new App(this.url);
            });

            test('is dispatched when the response is received', function() {

                var response = { code: 200, data1: 1, data2: 2 };

                this.server.respondWith(
                    'POST',
                    'http://localhost/controller.php',
                    [200, {'Content-Type': 'application/json'},
                    JSON.stringify(response)
                ]);

                var listener = sinon.spy();
                this.app.addListener('app.response', listener);

                this.app.dispatchInRemote('event.name');
                this.server.respond();

                sinon.assert.calledOnce(listener);
                sinon.assert.calledWithMatch(listener,
                    sinon.match.instanceOf(Event)
                        .and(sinon.match.has('response', sinon.match(response)))
                );
            });

            test('can change the response before processing it', function() {

                this.server.respondWith(
                    'POST',
                    'http://localhost/controller.php',
                    [200, {'Content-Type': 'application/json'},
                    JSON.stringify({ code: 200, data1: 1, data2: 2 })
                ]);

                var dummyResponse = {};
                this.app.addListener('app.response', function(event) {
                    event.response = dummyResponse; // Change the response within event.
                });

                var processResponseSpy = sinon.spy(this.app, 'processResponse');

                this.app.dispatchInRemote('event.name');
                this.server.respond();

                sinon.assert.calledOnce(processResponseSpy);
                sinon.assert.calledWith(processResponseSpy, dummyResponse);
            });
        });
    });

    suite('#dispatch()', function() {

        setup(function() {
            this.app = new App();
            this.eventName = 'event1';
            this.event = new Event(this.eventName);
            this.dispatchInRemote = sinon.spy(this.app, 'dispatchInRemote');
        });

        test('first dispatch the event in local and then dispatch in remote', function() {

            dispatchInLocal = sinon.spy(this.app, 'dispatchInLocal');
            this.app.dispatch(this.eventName, this.event);

            sinon.assert.calledWith(dispatchInLocal, this.eventName, this.event);
            sinon.assert.calledOnce(this.dispatchInRemote);
            sinon.assert.calledWith(this.dispatchInRemote, this.eventName, this.event);
            this.dispatchInRemote.calledImmediatelyAfter(dispatchInLocal);
        });

        test('the event is not dispatched in remote if propagation is stopped', function() {

            sinon.stub(this.app, 'dispatchInLocal').callsFake(function(eventName, event) {
                event.stopEventPropagation();
            });

            this.app.dispatch(this.eventName, this.event);

            sinon.assert.notCalled(this.dispatchInRemote);
        });
    });

    suite('#buildRequest()', function() {

        setup(function() {
            this.app = new App('http://localhost/', 'token');
        });

        ['token1', 'token2'].forEach(function(token) {
            test(`request has appToken equal to ${token} when app token is ${token}`, function() {
                var app = new App('http://localhost/', token);
                var request = app.buildRequest();
                assert.equal(request.appToken, token);
            });
        });

        ['status1', 'status2'].forEach(function(status) {
            test(`request has status equal to ${status} when #getStatus() return ${status}`, function() {

                var stub = sinon.stub(this.app, 'getStatus');
                stub.returns(status);

                var request = this.app.buildRequest();
                assert.equal(request.status, status);
            });
        });

        ['event1', 'event2'].forEach(function(eventName) {
            test(`request has eventName equal to ${eventName} when eventName argument is ${eventName}`, function() {
                var request = this.app.buildRequest(eventName);
                assert.equal(request.eventName, eventName);
            });
        });

        test('the request eventData is the event argument', function() {
            var event = new Event('event1');
            var request = this.app.buildRequest('event1', event);
            assert.equal(request.eventData, event);
        });

        test('serverUpdates contains all updates in the buffer', function() {

            var data1 = {
                attribute1: 1,
                attribute2: 1,
            };

            var data2 = {
                attribute3: 3,
            };

            this.app.buffer = {
                component1: data1,
                component2: data2,
            };

            var event = new Event('event1');
            var request = this.app.buildRequest('event1', event);
            var update1 = request.serverUpdates[0];
            var update2 = request.serverUpdates[1];

            assert.equal(update1.componentId, 'component1');
            assert.equal(update2.componentId, 'component2');
            assert.deepEqual(update1.data, data1);
            assert.deepEqual(update2.data, data2);
        });
    });

    suite('#registerUpdate()', function() {

        test('register in the buffer the update of the component attribute', function() {

            var app = new App();
            var model = {
                attribute1: 1,
                attribute2: 2,
            };
            var component1 = new Component('component1', app, model);

            sinon.stub(app, 'getComponent').withArgs('component1').returns(component1);
            app.registerUpdate('component1', 'attribute1', 11);
            app.registerUpdate('component1', 'attribute2', 22);

            var expectedBuffer = {
                component1: {
                    attribute1: 11,
                    attribute2: 22,
                }
            };

            assert.deepEqual(app.buffer, expectedBuffer);
        });

        test('do not alter the buffer if component is missing', function() {
            var app = new App();

            app.registerUpdate('component1', 'attribute1', 1);

            assert.isEmpty(app.buffer);
        });

        test('do not alter the buffer if attribute to update is not in the model', function() {

            var app = new App();
            var component1 = new Component('component1', app);
            sinon.stub(app, 'getComponent').withArgs('component1').returns(component1);

            app.registerUpdate('component1', 'attribute1', 1);

            assert.isEmpty(app.buffer);
        });
    });

    suite('#runAction()', function() {

        test('the action is executed with his arguments', function() {

            var app = new App();
            var action1Handler = sinon.spy();
            app.actionHandlers['action1'] = action1Handler;

            var action1 = {
                handler: 'action1',
                data: {
                    data1: 1
                },
            };

            app.runAction(action1);

            sinon.assert.calledWith(action1Handler, action1.data);
        });

        test('the "app.action" event can change the action before execution', function() {

            var app = new App();
            var action1Handler = sinon.spy();
            app.actionHandlers['action1'] = action1Handler;

            var data1 = {
                attr1: 1,
                attr2: 2,
            };

            var data2 = {
                attr1: 11,
                attr2: 22,
            };

            app.addListener('app.action', function(event) {
                event.action.data = data2;
            });

            app.runAction({handler: 'action1', data: data1});

            sinon.assert.calledWith(action1Handler, data2);
        });
    });

    suite('#processResponse()', function() {

        setup(function() {

            this.app = new App();

            this.model1 = {attr1: 1, attr2: 2};
            this.model2 = {attr1: 1, attr2: 2};

            this.component1 = new Component('component1', this.app, this.model1);
            this.component1.setAttr1 = function(val) {
                this.model.attr1 = val;
            };
            this.component1.setAttr2 = function(val) {
                this.model.attr2 = val;
            };

            this.component2 = new Component('component2', this.app, this.model2);
            this.component2.setAttr1 = function(val) {
                this.model.attr1 = val;
            };
            this.component2.setAttr2 = function(val) {
                this.model.attr2 = val;
            };

            this.app.addComponent(this.component1);
            this.app.addComponent(this.component2);
        });

        test('dispatch the "app.response_error" event when code is not 200', function() {

            var app = new App();
            var listenerSpy = sinon.spy();

            app.addListener('app.response_error', listenerSpy);

            var response = {};
            app.processResponse(response);

            sinon.assert.calledOnce(listenerSpy);
            sinon.assert.calledWithMatch(listenerSpy,
                sinon.match.instanceOf(Event)
                    .and(sinon.match.has('response', response))
            );
        });

        test('dispatch the "end_remote_update" event on components that they were executing themselves', function() {

            var listener1 = sinon.spy();
            var listener2 = sinon.spy();

            this.component1.addListener('end_remote_update', listener1);
            this.component2.addListener('end_remote_update', listener2);

            var response = {
                code: 200,
                request: {
                    serverUpdates: [
                        { componentId: 'component2', data: {attr2: 22} },
                    ],
                },
            };

            this.app.processResponse(response);

            sinon.assert.notCalled(listener1);
            sinon.assert.calledOnce(listener2);
            sinon.assert.calledWithMatch(listener2,
                sinon.match.instanceOf(Event)
                    .and(sinon.match.has('data', sinon.match({attr2: 22})))
            );
        });

        test('all client updates are executed', function() {

            var response = {
                code: 200,
                request: {
                    serverUpdates: [],
                },
                clientUpdates: {
                    update1: {componentId: 'component1', data: {attr1: 11, attr2: 22}},
                },
            };

            this.app.processResponse(response);

            assert.equal(this.component1.model.attr1, 11);
            assert.equal(this.component1.model.attr2, 22);
        });
    });
});
