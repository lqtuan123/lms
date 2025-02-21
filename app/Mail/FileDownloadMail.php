<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FileDownloadMail extends Mailable
{
    use Queueable, SerializesModels;

    // Đường link tải file
    public $link;

    /**
     * Create a new message instance.
     *
     * @param string $link
     * @return void
     */
    public function __construct($link)
    {
        $this->link = $link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('Resource::emails.file_download')
                    ->with([
                        'link' => $this->link, // Truyền link vào view
                    ])
                    ->subject('Link tải file của bạn'); // Tiêu đề email
    }
}
