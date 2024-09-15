<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\MsgQueue\Events;

use Modules\MsgQueue\Db\Models\MailQueue;
use Swift_Message;

class Mail {

    /**
     * @var array
     */
    protected array $config;

    /**
     * @param Swift_Message $message
     * @return void
     */
    public function send(Swift_Message $message): void {
        $message->setContentType("text/html");
        $dbMail = new MailQueue();
        if (is_array($message->getReplyTo())) {
            $dbMail->email_from = key($message->getReplyTo());
        }
        elseif (is_array($message->getFrom())){
            $dbMail->email_from = key($message->getFrom());
        }
        $dbMail->message = base64_encode(serialize($message));
        $dbMail->save();
    }

    /**
     * @param array $config
     * @return void
     */
    public function setConfig(array $config): void {
        $this->config = $config;
    }

}
