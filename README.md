# Notification service (recruitment task)

### Background

The company has multiple services including one that provides a user identity and several that require sending notifications. The goal is to create a new service abstracting the notification part.

## Task

Create a service that accepts the necessary information and sends a notification to customers.

The new service should be capable of the following:

1. Send notifications via different channels.\
   It should provide an abstraction between at least two different messaging service providers.\
   It can use different messaging services/technologies for communication (e.g. SMS, email, push notification, Facebook Messenger, etc).

    Examples of some messaging providers:
   *    Emails: AWS SES ([SendEmail - Amazon Simple Email Service](https://docs.aws.amazon.com/ses/latest/APIReference/API_SendEmail.html))
   *    SMS messages: Twilio ([Twilio SMS API Overview](https://www.twilio.com/docs/sms/api))
   *    Push notifications: Pushy ([Pushy - Docs - API - Send Notifications](https://pushy.me/docs/api/send-notifications))

   All listed services are free to try and are pretty painless to sign up for, so please register your own test accounts with at least one of them.


2. If one of the services goes down, your service can quickly failover to a different provider without affecting your customers:

   * It is possible to define several providers for the same type of notification channel. e.g. two providers for SMS.
   * A notification should be delayed and later resent if all providers fail.


3. The service is Configuration-driven: It is possible to enable/disable different communication channels with configuration.\
It is possible to send the same notification via several different channels.


4. (Bonus point) Throttling: some messages are expected to trigger a user response. In such a case the service should allow a limited amount of notifications sent to users within an hour. e.g. send up to 300 an hour.


5. (Bonus point) Usage tracking: we can track what messages were sent, when, and to whom. Recognition is done by a user identifier parameter. The identifier is provided as a parameter of the service.

### Additional information

* We prefer a solution that would work out of the box for us therefore please use containerization.
* It should be fairly easy for us to run and understand it therefore please add at least a short description. Kudos if you provide a set of tasks ready to run and test your solution.
* Another person should be capable of making changes without too much fear of breaking existing logic therefore please use tests.
* We use DDD (Domain-Driven Design) and therefore Kudos to you if your solution reflects some ideas of DDD.
* We understand that it might be time-consuming to finish this task therefore it’s acceptable to not finalize it. However, please think carefully about what part is more important than others as well as document your decisions about what and why you skip.

------------------------------------------------------------

## Solution

### Design overview

The service exposes to external world endpoint

      POST /inbox/send

Message accepted by this endpoint is registered in `inbox`.\
Then for each configured `channel`, new distribution request is registered in `outbox`.\
This ends synchronous part.

Asynchronous `worker` watch over `outbox`.\
If `worker` finds any request waiting for distribution, it calls one-by-one delivery services defined for `channel` suitable for serviced request.\
First successful response from one delivery service ends distribution by this `channel`.

### Configuration example

      CHANNEL_SMS=TwilioSmsService,BackupSmsService
      CHANNEL_EMAIL=AmazonSimpleEmailService,BackupEmailService
      CHANNEL_PUSH=PushyService
      CHANNEL_MIX=PushyService,AmazonSimpleEmailService,TwilioSmsService
      CHANNEL_FAIL=AlwaysFailService

      USED_CHANNELS=CHANNEL_SMS,CHANNEL_EMAIL,CHANNEL_FAIL

The service can be configured by environment variables.\
Variable `USED_CHANNELS` holds comma separated list of variables which represents channels.\
By convention, variable with channel definition is named `CHANNEL_{name}`.\
Each `CHANNEL_{name}` variable contains comma separated list of class names.\
Each class used in `CHANNEL_{name}` variable should be defined as Symfony event listener prepared for servicing objects of class `DeliverByServiceDto`.

### Implementation details

1. CQRS pattern (simple, without events) at domain level.
2. Two commands defined:
   * `SendCmd` - called by controller servicing endpoint `POST /inbox/send`
   * `DeliverByChannelCmd` - called by `worker` process (Symfony Messenger consumer) 
3. Symfony Serializer and Symfony Validator used for input JSON data deserialization and validation.
4. Symfony Messenger used for `outbox` control. It supplies queue driver, retry policy (retry number and variable delay), fail queue for messages which are permanently undeliverable.
5. NelmioApiDocBundle used for OpenAPI documentation.
6. PHPStan and PHP_CodeSniffer for better quality.
7. The service is designed to have more than one active `worker` process.

### Bonus

1. Usage tracking
   1. Database table `inbox` contains:
      1. user id,
      2. date and time when message was registered in the service,
      3. message content,
      4. phone number, email, push token, etc.
   2. Database table `outbox` allow to check list of channels used for delivery of given message. And for each delivery by channel it contains:
      1. delivery status,
      2. date and time when message was delivered,
      3. number of retries.
2. Throttling\
Throttling is not implemented.\
But can be simply added.\
In command `SendCmd` we can check number of messages for user with given ID registered in time period (e.g. 1 hour). If counted number is higher than threshold, command should reject new message.\
Throttling implemented this way has advantage, that it works fine in scalable environment - with more than one API container. 

### Limitations

1. API container uses very simple HTTP server. This configuration is suitable only for demo purposes.
2. Queue is implemented with Doctrine driver. This configuration is suitable only for demo purposes. But Symfony Messenger promises, that replacement this driver with SQS or RabbitMQ should not be problematic.
3. To reduce time which I spent on this task, I decided to not implement connection to any real delivery service.\
Method in class `ExternalServiceMock` simulates real service with `sleep()` and random failure. For demo purposes fail probability is set to high value - 50%.

### URLs

1. API documentation: 
   * http://127.0.0.1:8000/api.html
   * http://127.0.0.1:8000/api.json
   * http://127.0.0.1:8000/api.yaml
2. API call:

        curl --location 'http://127.0.0.1:8000/inbox/send' \
        --header 'Content-Type: application/json' \
        --data-raw '{
        "userId": "5689c3cf-5acf-4c14-bc32-8e0e6927a061",
        "email": "user@test.com",
        "pushToken": "123",
        "phoneNumber": "+48-123-456-789",
        "message": "Simple test message"
        }'
