<?php

namespace Modules\MsgQueue\Plugins;

use Swift_Message;

class GetMsgContent {

    /**
     * @param $content
     * @return Swift_Message
     */
    public function process($content): Swift_Message {
        return unserialize(base64_decode($content));
    }

}
