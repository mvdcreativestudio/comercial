<?php

namespace App\Services\Mail;

use App\Repositories\StoresEmailConfigRepository;
use Exception;

class EmailService
{
    protected MailProviderInterface $mailer;
    protected string $from;
    protected string $replyTo;
    protected $storesEmailConfigRepository;

    public function __construct(MailProviderInterface $mailer, string $from = null, string $replyTo = null, StoresEmailConfigRepository $storesEmailConfigRepository)
    {
        $this->mailer = $mailer;
        $this->from = $from ?? 'default@example.com';
        $this->replyTo = $replyTo ?? 'noreply@example.com';
        $this->storesEmailConfigRepository = $storesEmailConfigRepository;
        $storeId = auth()->user()->store_id ?? null;
        if (is_null($storeId)) {
            throw new Exception("Este usuario no está asociado a una tienda. Por favor, asócielo a una tienda antes de enviar correos.");
        }
        $storeConfig = $this->storesEmailConfigRepository->getConfigByStoreId($storeId);
        config([
            'mail.mailer' => $storeConfig->mail_mailer,
            'mail.host' => $storeConfig->mail_host,
            'mail.port' => $storeConfig->mail_port,
            'mail.username' => $storeConfig->mail_username,
            'mail.password' => $storeConfig->mail_password,
            'mail.encryption' => $storeConfig->mail_encryption,
            'mail.from.address' => $storeConfig->mail_from_address,
            'mail.from.name' => $storeConfig->mail_from_name,
        ]);
    }

    public function sendMail(
        string $to,
        string $subject,
        string $template,
        array $data = [],
        string $pdfPath = null,
        string $attachmentName = 'document.pdf' // Nombre por defecto del archivo adjunto
    ): bool {
        $content = $this->renderTemplate($template, $data);
        return $this->mailer->send($to, $subject, $content, $this->from, $this->replyTo, $pdfPath, $attachmentName);
    }

    protected function renderTemplate(string $template, array $data): string
    {
        return view($template, $data)->render();
    }
}
