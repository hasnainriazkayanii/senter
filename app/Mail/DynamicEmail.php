<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailTemplate;

class DynamicEmail extends Mailable
{
    use Queueable, SerializesModels;
    private $template_name='';
    private $content='';

    /**
     * Create a new message instance.
     *
     * @return void
     */
public function __construct($data,$subject,$content)
    {
        $this->data = $data;
        $this->subject = $subject;
        $this->content =  $content;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('info@poiesistech.com', 'HRK')
            ->subject($this->subject)
            ->view('emails.dynamic')
            ->with([
                'content'=>$this->parse_data(),
                'subject'=>$this->subject,
        ]);
    }

    public function parse_data(){
        $data = $this->data;
        $parsed = preg_replace_callback('/{{(.*?)}}/', function ($matches) use ($data) {
            list($shortCode, $index) = $matches;
            $index = trim($index);
            // echo $index;exit;
            if(isset($data[$index]) ) {
                return $data[$index];
            }

        }, $this->content);
        return $parsed;
    }

    public function content(){
        $this->content = '{{ first_name }} {{ last_name }} has created ticket against order {{ reference_number }}';
    }
}