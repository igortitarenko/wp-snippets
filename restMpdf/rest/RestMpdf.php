<?php

/**
 * Generates a PDF file.
 */

namespace restMpdf\rest;

use Mpdf\MpdfException;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed
}

class RestMpdf
{
    public function __construct()
    {
		add_action('rest_api_init', $this->restRegister(...));
    }

	/**
	 * Registers the REST call.
	 *
	 * @return void
	 */
	public function restRegister(): void
	{
		/*
		 * WordPress doesn't fail REST call if permission/sanitize/validate callbacks don't exist, so do it manually.
		 */
		$callbacks = [
			'perm'      => 'permissionsCallback',
			'valFil'    => 'validateLocalNonce',
		];

		foreach ($callbacks as $method) {
			if (false === method_exists($this, $method)) {
				wp_die('Callback doesn\'t exist');
			}
		}

		register_rest_route(
			'wpsnippets/v2',
			'restmpdf',
			[
				'methods'  => 'POST',
				'callback' => $this->restOutput(...),
				'permission_callback' => $this->permissionsCallback(...),
				'args' => [
					'nonce_local' => [
						'default' => false,
						'validate_callback' => $this->validateLocalNonce(...),
					],
				],
			]
		);
	}

	/**
	 * Rest call output.
	 *
	 * @return void
	 * @throws MpdfException
	 */
	public function restOutput(): void
	{
		/* Headers */
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename="data.pdf"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header("Content-Type: text/plain");

		/* mPDF */
		require_once(realpath(__DIR__ . '/../vendor/autoload.php'));

		$mpdf = new \Mpdf\Mpdf([
			'setAutoTopMargin'     => false,
			'margin_bottom'        => 10,
			'margin_left'          => 10,
			'margin_right'         => 10,
			'margin_top'           => 45,
			'tableMinSizePriority' => true,
		]);

		$mpdf->useFixedNormalLineHeight = false;
		$mpdf->useFixedTextBaseline     = false;
		$mpdf->adjustFontDescLineheight = 1.14;
		$mpdf->use_kwt = true;

		/* PDF-specific CSS */
		$css = file_get_contents(realpath(__DIR__ . '/../assets/css/pdf.css'));
		$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);

		/* New page */
		$mpdf->AddPageByArray([]);

		$header = '
            <div class="header_container"><table class="header">
                <tr>
                    <td class="header1">Header Title</td>
                    <td class="header2"><img src="" width="200" alt=""/></td>
                </tr>
            </table></div>
        ';

		$body = '<div><p>PDF Body</p></div>';

		$mpdf->SetHTMLHeader($header, 'BLANK', 'BLANK', true);
		$mpdf->WriteHTML(stripslashes($body), \Mpdf\HTMLParserMode::HTML_BODY);

		echo base64_encode($mpdf->Output('data.file', 'S'));
	}

	/**
	 * Checks for permissions.
	 * In this case is empty.
	 *
	 * @return bool
	 */
	public function permissionsCallback(): bool
	{
		return true;
	}

	/**
	 * Checks for a nonce.
	 * This nonce is optional. Having this second nonce prevents public access to this REST call.
	 *
	 * @param string $nonce
	 * @see $this->enqueueScript(...)
	 *
	 * @return bool
	 */
	public function validateLocalNonce(string $nonce): bool
	{
		if (false !== wp_verify_nonce($nonce, 'restMpdf')) {
			return true;
		}

		return false;
	}
}

new RestMpdf();
