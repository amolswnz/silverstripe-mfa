<?php declare(strict_types=1);

namespace SilverStripe\MFA\Service;

use Exception;
use Email;
use Member;
use SS_Log;
use SS_Object;

/**
 * Encapsulates setting up an Email in order to allow for dependency injection and to avoid introducing a hard
 * coupling to the SilverStripe core Email class in code that consumes this class.
 */
class Notification extends SS_Object
{
    /**
     * Whether sending emails is enabled for MFA changes
     *
     * @config
     * @var bool
     */
    private static $enabled = true;

    /**
     * Sends the notification to the member
     *
     * Builds the notification delivery class & adds details to it. This is by default an {@see Email} as built by
     * Injector. Data can contain 'from', 'to', and 'subject' keys, which will be set on the Email before sending.
     * A caveat to be aware of is that setting 'to' will obviously override the given Member as the recipient.
     *
     * @param Member $member Member the notification is being sent to
     * @param string $template Name of the HTMLTemplate to use for the email body
     * @param array $data (optional) Extra data for use in populating the template
     *
     * @return bool
     */
    public function send(Member $member, string $template, array $data = []): bool
    {
        if (!$this->config()->get('enabled')) {
            return false;
        }

        // Catch exceptions with setting the "to" address and sending the email.
        try {
            $email = Email::create()
                ->setTo($member->Email)
                ->setTemplate($template)
                ->populateTemplate(array_merge(['Member' => $member], $data));

            foreach (['to', 'from', 'subject'] as $header) {
                if (isset($data[$header])) {
                    $method = 'set' . ucwords($header);
                    $email->$method($data[$header]);
                }
            }

            $this->extend('onBeforeSend', $email);

            $sendResult = $email->send();
        } catch (Exception $e) {
            SS_Log::log($e->getMessage(), SS_Log::INFO);
        }

        $this->extend('onAfterSend', $email, $sendResult);

        // Could no longer be a bool as a result of the extension point above, so is cast to bool.
        return (bool) $sendResult;
    }
}
