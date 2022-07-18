<?php

namespace SunAppModules\Core\Mail;

use GuzzleHttp\ClientInterface;
use Illuminate\Mail\Transport\Transport;
use Swift_Mime_SimpleMessage;

class SunmailTransport extends Transport
{
    /**
     * Guzzle client instance.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * The API URL to which to POST emails.
     *
     * @var string
     */
    protected $url;

    protected $config;

    /**
     * Create a new Custom transport instance.
     *
     * @param  \GuzzleHttp\ClientInterface  $client
     * @param  string|null  $url
     * @param  string  $key
     * @return void
     */
    public function __construct(ClientInterface $client, $url)
    {
        $this->client = $client;
        $this->url = $url;
        $this->config = app()['config']->get('mail', []);
    }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param Swift_Mime_Message $message
     * @param string[] $failedRecipients An array of failures by-reference
     *
     * @return int
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $payload = [
            'header' => ['Content-Type', 'application/json'],
            'auth' => [$this->config['username'], $this->config['password']],
        ];

        $this->addTo($message, $payload);
        $this->addReplyTo($message, $payload);
        $this->addSubject($message, $payload);
        $this->addContent($message, $payload);
        $this->addData($message, $payload);

        return $this->client->post($this->url, $payload);
    }

    /**
     * Add the to email and to name (If provided) to the payload.
     *
     * @param Swift_Mime_Message $message
     * @param array $payload
     */
    protected function addTo(Swift_Mime_SimpleMessage $message, &$payload)
    {
        $to = $message->getTo();

        $toAddress = key($to);
        if ($toAddress) {
            $payload['json']['email'] = $toAddress;

            $toName = $to[$toAddress] ?: null;
            if ($toName) {
                $payload['json']['name'] = $toName;
            }
        }
    }

    /**
     * Add the reply_to email and reply_to name (If provided) to the payload.
     *
     * @param Swift_Mime_Message $message
     * @param array $payload
     */
    protected function addReplyTo(Swift_Mime_SimpleMessage $message, &$payload)
    {
        if ($replyTo = $message->getReplyTo()) {
            $replyToAddress = key($replyTo);
            if ($replyToAddress) {
                $payload['json']['data']['reply'] = $replyToAddress;

                $replyToName = $replyTo[$replyToAddress] ?: null;
                if ($replyToName) {
                    $payload['json']['data']['reply_name'] = $replyToName;
                }
            }
        }
    }

    /**
     * Add the from email and from name (If provided) to the payload.
     *
     * @param Swift_Mime_Message $message
     * @param array $payload
     */
    protected function addFrom(Swift_Mime_SimpleMessage $message, &$payload)
    {
        $from = $message->getFrom();

        $fromAddress = key($from);
        if ($fromAddress) {
            $payload['json']['email'] = $fromAddress;

            $fromName = $from[$fromAddress] ?: null;
            if ($fromName) {
                $payload['json']['name'] = $fromName;
            }
        }
    }

    /**
     * Add the subject of the email (If provided) to the payload.
     *
     * @param Swift_Mime_Message $message
     * @param array $payload
     */
    protected function addSubject(Swift_Mime_SimpleMessage $message, &$payload)
    {
        if ($subject = $message->getSubject()) {
            $payload['json']['subject'] = $subject;
        }
    }

    /**
     * Add the content/body to the payload based upon the content type provided in the message object. In the unlikely
     * event that a content type isn't provided, we can guess it based on the existence of HTML tags in the body.
     *
     * @param Swift_Mime_Message $message
     * @param array $payload
     */
    protected function addContent(Swift_Mime_SimpleMessage $message, &$payload)
    {
        $contentType = $message->getContentType();
        $body = $message->getBody();

        if (!in_array($contentType, ['text/html', 'text/plain'])) {
            $contentType = strip_tags($body) != $body ? 'text/html' : 'text/plain';
        }

        // $payload['json'][$contentType == 'text/html' ? 'content_html' : 'Text-part'] = $message->getBody();
        $payload['json']['content_html'] = $message->getBody();
    }

    /**
     * Add to, cc and bcc recipients to the payload.
     *
     * @param Swift_Mime_Message $message
     * @param array $payload
     */
    protected function addRecipients(Swift_Mime_SimpleMessage $message, &$payload)
    {
        foreach (['To', 'Cc', 'Bcc'] as $field) {
            $formatted = [];
            $method = 'get' . $field;
            $contacts = (array) $message->$method();
            foreach ($contacts as $address => $display) {
                $formatted[] = $display ? $display . " <$address>" : $address;
            }

            if (count($formatted) > 0) {
                $payload['json'][$field] = implode(', ', $formatted);
            }
        }
    }

    protected function addData(Swift_Mime_SimpleMessage $message, &$payload)
    {
        $data_header = $message->getHeaders()->get('data');
        if ($data_header) {
            $payload['json']['data'] = array_merge(
                ($payload['json']['data'] ?? []),
                json_encode((array)$data_header->getValue())
            );
        }
    }
}
