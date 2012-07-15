<?php
/**
 * This file is part of Torii
 *
 * @version $Revision: 1469 $
 */

namespace Torii;

/**
 * Mail messenger
 *
 * @version $Revision$
 */
class MailMessenger
{
    /**
     * Twig envoronment
     *
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * Mail sender address
     *
     * @var string
     */
    protected $sender;

    /**
     * Construct from twig environment
     *
     * @param \Twig_Environment $twig
     * @return void
     */
    public function __construct( \Twig_Environment $twig, $sender )
    {
        $this->twig   = $twig;
        $this->sender = $sender;
    }

    /**
     * Send email
     *
     * @param string $email
     * @param Struct\Response $result
     * @return void
     */
    public function send( $email, Struct\Response $result )
    {
        $template = $this->twig->loadTemplate( 'email/' . $result->template );
        mail(
            $email,
            $template->renderBlock( 'subject', $result->data ),
            $template->renderBlock( 'body_text', $result->data ),
            "From: {$this->sender}\r\n"
        );
    }
}

