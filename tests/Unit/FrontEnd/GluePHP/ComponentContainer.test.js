
suite('GluePHP.ComponentContainer', function() {

    const Container = GluePHP.ComponentContainer;
    const Component = GluePHP.Component;

    setup(function() {

        this.container = new Container();

        this.insertTwoComponents = function() {
            this.component1 = new Component('component1');
            this.component2 = new Component('component2');
            this.container.addComponent(this.component1);
            this.container.addComponent(this.component2);
        };

        this.initializeNestedComponents = function() {

            this.component1 = new Component('component1');
            this.component2 = new Component('component2');
            this.component3 = new Component('component3');
            this.component4 = new Component('component4');
            this.component5 = new Component('component5');

            this.component1.addComponent(this.component2);
            this.component2.addComponent(this.component3);
            this.component4.addComponent(this.component5);

            this.container.addComponent(this.component1);
            this.container.addComponent(this.component4);
        };
    });

    suite('#getAllComponents()', function() {

        test('return an empty array by default', function() {
            assert.isEmpty(this.container.getAllComponents());
        });

        test('return all inserted components by #addComponent()', function() {

            this.insertTwoComponents();

            assert.deepEqual(this.container.getAllComponents(), {
                component1: this.component1,
                component2: this.component2,
            });
        });
    });

    suite('#getComponent()', function() {

        test('return null if component not exists', function() {
            assert.isNull(this.container.getComponent('component1'));
        });

        test('return the component if exists', function() {

            var component1 = new Component('component1');

            this.container.addComponent(component1);

            assert.equal(this.container.getComponent('component1'), component1);
        });

        test('search the component in all the tree', function() {

            this.initializeNestedComponents();

            assert.equal(this.container.getComponent('component2'), this.component2);
            assert.equal(this.container.getComponent('component3'), this.component3);
            assert.equal(this.container.getComponent('component5'), this.component5);
        });

        test('complex id case 1', function() {
            this.initializeNestedComponents();
            assert.equal(
                this.container.getComponent('component4 component5'),
                this.component5
            );
        });

        test('complex id case 2', function() {
            this.initializeNestedComponents();
            assert.equal(
                this.container.getComponent('component1 component3'),
                this.component3
            );
        });

        test('complex id case 3', function() {
            this.initializeNestedComponents();
            assert.equal(
                this.container.getComponent('component1 component2 component3'),
                this.component3
            );
        });

        test('complex id case 4', function() {
            this.initializeNestedComponents();
            assert.equal(
                this.container.getComponent('component2 component3'),
                this.component3
            );
        });

        test('complex id case 5', function() {
            this.initializeNestedComponents();
            assert.isNull(this.container.getComponent('component1 component5'));
        });
    });

    suite('#existsComponent()', function() {

        test('return false if component not exists', function() {
            assert.isFalse(this.container.existsComponent('component1'));
        });

        test('return true if component exists', function() {
            this.insertTwoComponents();
            assert.isTrue(this.container.existsComponent('component1'));
        });
    });

    suite('#dropComponent()', function() {

        test('remove the component when exists', function() {

            this.insertTwoComponents();

            this.container.dropComponent('component1');

            assert.deepEqual(this.container.getAllComponents(), {
                component2: this.component2
            });
        });
    });
});
