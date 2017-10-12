
suite('GluePHP.Component', function() {

    const App = GluePHP.App;
    const Component = GluePHP.Component;
    const EventDispatcher = GluePHP.EventDispatcher;

    test('is instance of GluePHP.BaseEntity', function() {
        var component = new Component();
        assert.instanceOf(component, GluePHP.BaseEntity);
    });

    suite('#id', function() {

        ['component1', 'component2'].forEach(function(id) {
            test(`is ${id} when id argument is ${id}`, function() {
                var component = new Component(id);
                assert.equal(component.id, id);
            });
        });
    });

    suite('#app', function() {
        test('is the app argument', function() {
            var app = new App();
            var component = new Component('component1', app);

            assert.equal(component.app, app);
        });
    });

    suite('#model', function() {

        test('is equal to model argument', function() {
            var model = {};
            var component = new Component('component1', null, model);
            assert.equal(component.model, model);
        });

        test('the model is sealed', function() {
            var component = new Component('component1', null, {});
            assert.isSealed(component.model);
        });
    });

    suite('#html', function() {

        test('is equal to html argument', function() {
            var html = {};
            var component = new Component('component1', null, null, html);
            assert.equal(component.html, html);
        });
    });

    suite('#dispatchInApp()', function() {
        test('dispatch the event in the app. The event name is transformed to <componentId>.<eventName>', function() {

            var eventName = 'event1';
            var event = new Event(eventName);
            var app = new App();

            var appMock = sinon.mock(app);
            appMock.expects('dispatch').once().withArgs('component1.event1', event);

            var component1 = new Component('component1', app);
            component1.dispatchInApp(eventName, event);

            appMock.verify();
        });
    });

    suite('#dispatch()', function() {

        setup(function() {
            this.app = new App();
            this.eventName = 'event1';
            this.event = new Event(this.eventName);
            this.component1 = new Component('component1', this.app);
            this.dispatchInApp = sinon.spy(this.component1, 'dispatchInApp');
        });

        test('first dispatch the event in local and then dispatch in the app', function() {

            dispatchInLocal = sinon.spy(this.component1, 'dispatchInLocal');
            this.component1.dispatch(this.eventName, this.event);

            sinon.assert.calledOnce(dispatchInLocal);
            sinon.assert.calledWith(dispatchInLocal, this.eventName, this.event);
            sinon.assert.calledOnce(this.dispatchInApp);
            sinon.assert.calledWith(this.dispatchInApp, this.eventName, this.event);
            this.dispatchInApp.calledImmediatelyAfter(dispatchInLocal);
        });

        test('the event is not dispatched in the app if propagation is stopped', function() {

            sinon.stub(this.component1, 'dispatchInLocal').callsFake(function(eventName, event) {
                event.stopEventPropagation();
            });

            this.component1.dispatch(this.eventName, this.event);

            sinon.assert.notCalled(this.dispatchInApp);
        });
    });
});