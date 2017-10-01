
suite('GlueApps.EventDispatcher', function() {

	const EventDispatcher = GlueApps.EventDispatcher;

	setup(function() {
		this.dispatcher = new EventDispatcher();
		this.eventName = 'event1';
		this.event = new Event(this.eventName);
	});

	suite('#dispatch()', function() {

		test('execute all listeners in order with the event object', function() {

			var listener1 = sinon.spy();
			var listener2 = sinon.spy();

			this.dispatcher.addListener(this.eventName, listener1);
			this.dispatcher.addListener(this.eventName, listener2);

			this.dispatcher.dispatch(this.eventName, this.event);

			assert(listener1.calledWith(this.event));
			assert(listener2.calledWith(this.event));
			assert(listener2.calledImmediatelyAfter(listener1));
		});

		test('event propagation is stopped within a listener', function() {

			var listener1 = function(event) {
				event.stopEventPropagation();
			};
			var listener2 = sinon.spy();

			this.dispatcher.addListener(this.eventName, listener1);
			this.dispatcher.addListener(this.eventName, listener2);
			this.dispatcher.dispatch(this.eventName, this.event);

			sinon.assert.notCalled(listener2);
		});
	});
});