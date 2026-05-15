<?php

declare(strict_types=1);

namespace Inicio\Service;

use Laminas\Mail\Address;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Mime;
use Laminas\Mime\Part;

class OutlookMailService
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function sendNotification(string $to, string $subject, string $body, bool $isHtml = false): void
    {
        if (!($this->config['enabled'] ?? false)) {
            throw new \RuntimeException('El servicio de correo Outlook está deshabilitado en la configuración.');
        }

        $fromEmail = trim((string) ($this->config['from']['email'] ?? ''));
        $fromName = trim((string) ($this->config['from']['name'] ?? 'SIGEC'));
        $host = trim((string) ($this->config['host'] ?? ''));
        $port = (int) ($this->config['port'] ?? 0);
        $connectionClass = trim((string) ($this->config['connection_class'] ?? 'login'));
        $connectionConfig = $this->config['connection_config'] ?? [];
        $username = trim((string) ($connectionConfig['username'] ?? ''));
        $password = trim((string) ($connectionConfig['password'] ?? ''));

        if ($fromEmail === '' || $host === '' || $port === 0 || $username === '' || $password === '') {
            throw new \RuntimeException('La configuración SMTP de Outlook está incompleta.');
        }

        $message = new Message();
        $message->setEncoding('UTF-8');
        $message->setFrom(new Address($fromEmail, $fromName));
        $message->addTo($to);
        $message->setSubject($subject);

        if ($isHtml) {
            $htmlPart = new Part($body);
            $htmlPart->type = Mime::TYPE_HTML;
            $htmlPart->charset = 'UTF-8';

            $mimeMessage = new MimeMessage();
            $mimeMessage->setParts([$htmlPart]);
            $message->setBody($mimeMessage);
        } else {
            $message->setBody($body);
        }

        $transport = new Smtp();
        $transport->setOptions(new SmtpOptions([
            'name' => $host,
            'host' => $host,
            'port' => $port,
            'connection_class' => $connectionClass,
            'connection_config' => $connectionConfig,
        ]));

        $transport->send($message);
    }
}
