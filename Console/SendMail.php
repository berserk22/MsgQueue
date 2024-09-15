<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\MsgQueue\Console;

use Core\Console\Command;
use DI\DependencyException;
use DI\NotFoundException;
use Modules\MsgQueue\Db\Models\MailQueue;
use Swift_Message;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendMail extends Command {

    protected array $config = [];

    /**
     * @param $application
     */
    public function __construct($application) {
        parent::__construct($application);
    }

    /**
     * @return void
     */
    public function configure(): void {
        $this->setName('send_mail')->setDescription('SendMail');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function execute (InputInterface $input, OutputInterface $output): int {
        $messages = MailQueue::where('logged', '=', null)->orWhere('logged', '=', '')->get();

        $transport = null;
        if ($this->getConfig('transport') === "sendmail"){
            // Sendmail Transporter
            $transport = new \Swift_SendmailTransport('/usr/sbin/sendmail -t');
        }
        elseif ($this->getConfig('transport') === "smtp"){
            // SMTP Transporter
            $transport = (new \Swift_SmtpTransport(
                $this->getConfig('server'),
                $this->getConfig('port'),
                $this->getConfig('encryption')
            ))
                ->setUsername($this->getConfig('username'))
                ->setPassword($this->getConfig('password'));
        }

        foreach ($messages as $message){

            $mailer = new \Swift_Mailer($transport);
            $message->logged = time();
            $message->save();

            try {
                /** @var Swift_Message $tmp_message */
                $tmp_message = unserialize(base64_decode($message->message));
                $tmp_message->setFrom($this->getConfig('username'), $this->getConfig('name'));

                if ($tmp_message->getTo() === null){
                    $tmp_message->setTo($this->getConfig('email'));
                }
                $mailer->send($tmp_message);

                $message->message=base64_encode(serialize($tmp_message));
                $message->logged = 'success';
                $message->save();
                usleep(500000);
            } catch (\Exception $e) {
                $message->error = $e->getMessage();
                $message->logged = '';
                $message->save();
            }
        }
        return 1;
    }

    /**
     * @param string|null $item
     * @return mixed
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function getConfig(string $item = null): mixed {
        if (empty($this->config)){
            $this->config = $this->getContainer()->get('config')->getSetting('queue')['mail'];
        }
        if ($item !== null) {
            return $this->config[$item];
        }
        else {
            return $this->config;
        }
    }

}
