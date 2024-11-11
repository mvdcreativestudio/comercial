<?php

namespace App\Services\Mail;

use App\Repositories\StoresEmailConfigRepository;
use Exception;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    protected MailProviderInterface $mailer;
    protected string $from;
    protected string $replyTo;
    protected $storesEmailConfigRepository;

    public function __construct(MailProviderInterface $mailer, StoresEmailConfigRepository $storesEmailConfigRepository)
    {
        $this->mailer = $mailer;
        $this->storesEmailConfigRepository = $storesEmailConfigRepository;

        $storeId = auth()->user()->store_id ?? null;
        if (is_null($storeId)) {
            throw new Exception("Este usuario no está asociado a una tienda. Por favor, asócielo a una tienda antes de enviar correos.");
        }

        // Recupera la configuración de la tienda desde la base de datos
        $storeConfig = $this->storesEmailConfigRepository->getConfigByStoreId($storeId);

        // Configura el mailer dinámicamente
        config([
            'mail.default' => $storeConfig->mail_mailer,
            'mail.mailers.smtp.host' => $storeConfig->mail_host,
            'mail.mailers.smtp.port' => $storeConfig->mail_port,
            'mail.mailers.smtp.username' => $storeConfig->mail_username,
            'mail.mailers.smtp.password' => $storeConfig->mail_password,
            'mail.mailers.smtp.encryption' => $storeConfig->mail_encryption,
            'mail.from.address' => $storeConfig->mail_from_address,
            'mail.from.name' => $storeConfig->mail_from_name,
        ]);

        $this->from = $storeConfig->mail_from_address ?? 'default@example.com';
        $this->replyTo = $storeConfig->mail_reply_to_address ?? 'noreply@example.com';
    }

    public function sendMail(
        string $to,
        string $subject,
        string $template,
        string $pdfPath = null,
        string $attachmentName = 'document.pdf',
        array $data = []
    ): bool {
        $data = array_merge([
            'from' => $this->from,
            'replyTo' => $this->replyTo,
        ], $data);
        // dd($data);
        $content = $this->renderTemplate($template, $data);
        return $this->mailer->send($to, $subject, $content, $this->from, $this->replyTo, $pdfPath, $attachmentName);
    }

    protected function renderTemplate(string $template, array $data): string
    {
        return view($template, compact('data'))->render();
    }
}
