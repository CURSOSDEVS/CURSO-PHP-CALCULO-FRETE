<?php

namespace Hcode;

use Rain\Tpl;
//use phpmailer\phpmailer\src\PHPMailer;

/**Nesta classe iremos passar alguns argumentos
 * O endereço que iremos enviar, nome do destinatário,
 * assunto, nome do template que iremos passar para o Rain tpl e os dados
 */
class Mailer
{
    const USERNAME = "xxxxxxxx@gmail.com";
    const PASSWORD = "xxxxxx";
    const NAMEFROM = "Hcode Store";

    private $mail;

    public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
    {

        /**Iremos criar nosso template */
        $config = array(
            /**aqui vamos configurar qual o caminho onde
             * estarão as paginas
             */
            "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/email/",
            "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
            "debug"         => false // set to false to improve the speed
        );

        Tpl::configure( $config );

        //criamos o template
        $tpl = new Tpl;

        foreach ($data as $key => $value) {
            $tpl->assign($key, $value);
        }

        $html = $tpl->draw($tplName, true);
    
        /** finalizando a criação do template */

        //Create a new PHPMailer instance
        $this->mail = new \PHPMailer();

        //Tell PHPMailer to use SMTP
        $this->mail->isSMTP();

        //Enable SMTP debugging
        //SMTP::DEBUG_OFF = off (for production use)
        //SMTP::DEBUG_CLIENT = client messages
        //SMTP::DEBUG_SERVER = client and server messages
        //$this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $this->mail->SMTPDebug = 0;

        //ask for Html-friendly debug output
        $this->mail->Debugoutput = 'html';

        //Set the hostname of the mail server
        $this->mail->Host = 'smtp.gmail.com';
        //Use `$this->mail->Host = gethostbyname('smtp.gmail.com');`
        //if your network does not support SMTP over IPv6,
        //though this may cause issues with TLS

        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $this->mail->Port = 587;

        //Set the encryption mechanism to use - STARTTLS or SMTPS
        //$this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->SMTPSecure = 'tls';

        //Whether to use SMTP authentication
        $this->mail->SMTPAuth = true;

        //Username to use for SMTP authentication - use full email address for gmail
        $this->mail->Username = Mailer::USERNAME;

        //Password to use for SMTP authentication
        $this->mail->Password = Mailer::PASSWORD;

        //Seta o remetente
        $this->mail->setFrom(Mailer::USERNAME, Mailer::NAMEFROM);

        //Set an alternative reply-to address
        //$this->mail->addReplyTo('replyto@example.com', 'First Last');

        //Set who the message is to be sent to
        $this->mail->addAddress($toAddress, $toName);

        //Seta o título
        $this->mail->Subject = $subject;

        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        //variável $html que será utilizada com o Rain Tpl
        $this->mail->msgHTML($html);

        //Replace the plain text body with one created manually
        $this->mail->AltBody = 'Texto usado em caso do arquivo html não abrir';

        //anexar anexos
        //$this->mail->addAttachment('images/phpmailer_mini.png');

       
    }

    //metodo para enviar o email
    public function send()
    {
        return $this->mail->send();
    }
}


?>