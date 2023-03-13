# A Simple PHP Workflow Engine (State Machine)

This is a PHP engine that can take a subject (entity, document, db record) that
has an enum property (either a native PHP enum or [Konekt Enum](https://konekt.dev/enum))
that represents the state of the subject.

The workflow is defined around the possible states (values of the enum).

Transitions can be defined from one state to another.

## Example

```php

class OrderWorkflow extends Workflow
{
    private static string $enumClass = OrderStatus::class;
    private static string $property = 'status';

    private static array $graph = [
        'name' => 'Order Workflow',
        'transitions' => [
            'prepare' => [
                'from' => [OrderStatus::NEW, OrderStatus::PENDING],
                'to' => OrderStatus::PROCESSING,
            ],
            'ship' => [
                'from' => [OrderStatus::PROCESSING],
                'to' => OrderStatus::COMPLETED,
            ],
            'cancel' => [
                'from' => ['*'],
                'to' => OrderStatus::CANCELED,
            ],
        ],    
    ];
}

$order = Order::find(1);
echo $order->status->value;
// PROCESSING

$workflow = OrderWorkflow::for($order);
$workflow->can('prepare');
// false
$workflow->can('cancel');
// true

$workflow->allowedTransitions()
// ['ship, 'cancel']

$workflow->execute('ship');
echo $order->status->value;
// COMPLETED
```

## Writing Explicit Transitions

By default, executing transitions will mutate the subject's state to the desired end state.

But it's also possible to explicitly define methods that will be called instead.

```php
class OrderWorkflow extends Workflow
{
    private static string $enumClass = OrderStatus::class;
    private static string $property = 'status';

    private static array $graph = [
        'transitions' => [
            'cancel' => [
                'from' => ['*'],
                'to' => OrderStatus::CANCELED,
            ],
        ],
    ];
    
    
    public function cancel(Order $order): void
    {
        foreach ($order->items as $item) {
            Inventory::release($item->sku, $item->quantity)
        }
    
        $order->status = OrderStatus::CANCELED;
        $orders->save();
        
        Event::dispatch(new OrderCanceled($order));  
    }
}
```
