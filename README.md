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


3. If one of the services goes down, your service can quickly failover to a different provider without affecting your customers:

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
