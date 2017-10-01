
suite('GluePHP.BaseEntity', function() {

    const BaseEntity = GluePHP.BaseEntity;
    const EventDispatcher = GluePHP.EventDispatcher;

    test('dispatcher is an instance of GluePHP.EventDispatcher', function() {
        var entity = new BaseEntity();
        assert.instanceOf(entity.dispatcher, EventDispatcher);
    });

    suite('#dispatchInLocal()', function() {
        test('is alias to dispatcher.dispatch()', function() {

            var entity = new BaseEntity();
            var eventName = 'event1';
            var event = new Event(eventName);
            var spy = sinon.spy(entity.dispatcher, 'dispatch');

            entity.dispatchInLocal(eventName, event);

            sinon.assert.calledOnce(spy);
            sinon.assert.calledWith(spy, eventName, event);

            spy.restore();
        });
    });
});
