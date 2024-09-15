<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\MsgQueue\Controller;

use Core\Exception;
use Core\Module\Dashboard;
use DI\DependencyException;
use DI\NotFoundException;
use Modules\Dashboard\DashboardTrait;
use Modules\MsgQueue\MsgQueueTrait;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Swift_Message;

class DashboardController extends Dashboard {

    use DashboardTrait, MsgQueueTrait;

    private string $dataTimeFormat = 'Y-m-d H:i:s';

    /**
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function registerFunctions(): void {
        $this->getDashboardMsgQueueRouter()->getMapBuilder($this);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function list(Request $request, Response $response): Response {
        $formData = $request->getParsedBody();
        if (!empty($formData)){
            $formData["dates"] = explode(" bis ", $formData["dates"]);
            if (!isset($formData["dates"][1])){
                $formData["dates"][1]=$formData["dates"][0];
            }
            $start = strtotime(date("d.m.Y 00:00:00", strtotime($formData["dates"][0])));
            $end = strtotime(date("d.m.Y 23:59:59", strtotime($formData["dates"][1])));

            $liste = $this->getMsgManager()->getMsgMailEntity()::whereBetween('created_at', [date($this->dataTimeFormat, $start), date($this->dataTimeFormat, $end)])->orderBy('id', 'DESC')->get();

            $this->getView()->setVariables([
                'fromDate'=>$formData["dates"][0],
                'toDate'=>$formData["dates"][1],
            ]);
        }
        else {
            $from_date = date("d.m.Y", (time() - (86400 * 14)));

            $start = strtotime(date("d.m.Y 00:00:00", strtotime($from_date)));
            $end = strtotime(date("d.m.Y 23:59:59", time()));

            $liste = $this->getMsgManager()->getMsgMailEntity()::whereBetween('created_at', [date($this->dataTimeFormat, $start), date($this->dataTimeFormat, $end)])->orderBy('id', 'DESC')->get();

            $this->getView()->setVariables([
                'fromDate'=>$from_date,
                'toDate'=>date("d.m.Y"),
            ]);
        }

        $this->getView()->setVariables([
            'seo'=>[
                'title'=>'E-Mails'
            ],
            'breadcrumbs'=>[
                'Dashboard'=>['dashboard_home'],
                'E-Mails'=>''
            ],
            'liste'=>$liste,
        ]);
        return $this->getView()->render($response, 'msg/list');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function view(Request $request, Response $response): Response {
        $msgId = $request->getAttribute("msgId");
        $msg = $this->getMsgManager()->getMsgMailEntity()::find($msgId);
        $message = unserialize(base64_decode($msg->message));
        $template = $this->getView()->getHtml('msg/view', [
            'message'=>$message,
        ]);

        return $this->getView()->renderJson($response, [
            'success'=>true,
            'title'=>'E-Mail',
            'template'=>$template
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function redirect(Request $request, Response $response): Response {
        $msgId = $request->getAttribute('msgId');
        $message = $this->getMsgManager()->getMsgMailEntity()::find($msgId);
        $formData = $request->getParsedBody();
        if ($this->getConfig("queue", "mail")['transport'] === "sendmail"){
            // Sendmail Transporter
            $transport = new \Swift_SendmailTransport('/usr/sbin/sendmail -t');
        }
        elseif ($this->getConfig("queue", "mail")['transport'] === "smtp"){
            // SMTP Transporter
            $transport = (new \Swift_SmtpTransport(
                $this->getConfig("queue", "mail")['server'],
                $this->getConfig("queue", "mail")['port'],
                $this->getConfig("queue", "mail")['encryption'])
            )
                ->setUsername($this->getConfig("queue", "mail")['username'])
                ->setPassword($this->getConfig("queue", "mail")['password']);
        }

        $mailer = new \Swift_Mailer($transport);

        /**
         * @var Swift_Message $tmp_message
         */
        $tmp_message = unserialize(base64_decode($message->message));
        $tmp_message->setFrom(
            $this->getConfig("queue")["mail"]['username'],
            $this->getConfig("queue")["mail"]['name']
        );
        $tmp_message->setTo($formData["email"], "NoReply");
        $mailer->send($tmp_message);
        usleep(500000);

        $data=[
            'success'=>true,
            'successMessage'=>"E-Mail wurde erfolgreich weitergeleitet.",
        ];

        return $this->getView()->renderJson($response, $data);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function remove(Request $request, Response $response): Response {
        $msgId = $request->getAttribute("msgId");
        try {
            $this->getMsgManager()->getMsgMailEntity()::where('id', '=', $msgId)->delete();
            $this->getView()->setVariables([
                'success'=>true
            ]);
        } catch (Exception $e){
            $this->getView()->setVariables([
                'success' => false,
                'errorMessage' => $e->getMessage()
            ]);
        }
        return $this->getView()->renderJson($response);
    }
}
