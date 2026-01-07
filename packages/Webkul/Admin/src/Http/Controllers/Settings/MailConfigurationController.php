<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Repositories\CoreConfigRepository;

class MailConfigurationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected CoreConfigRepository $coreConfigRepository) {}

    /**
     * Display mail configuration page.
     */
    public function index(): View
    {
        $smtpConfig = $this->getSmtpConfiguration();
        $imapConfig = $this->getImapConfiguration();

        return view('admin::settings.mail-configuration.index', compact('smtpConfig', 'imapConfig'));
    }

    /**
     * Store mail configuration settings.
     */
    public function store(): RedirectResponse
    {
        $this->validate(request(), [
            'email.smtp.account.host'         => 'nullable|string|max:255',
            'email.smtp.account.port'         => 'nullable|integer|min:1|max:65535',
            'email.smtp.account.encryption'   => 'nullable|string|in:tls,ssl',
            'email.smtp.account.username'     => 'nullable|string|max:255',
            'email.smtp.account.password'     => 'nullable|string|max:255',
            'email.smtp.account.from_address' => 'nullable|email|max:255',
            'email.smtp.account.from_name'    => 'nullable|string|max:255',
            'email.imap.account.host'         => 'nullable|string|max:255',
            'email.imap.account.port'         => 'nullable|integer|min:1|max:65535',
            'email.imap.account.encryption'   => 'nullable|string|in:tls,ssl,notls',
            'email.imap.account.username'     => 'nullable|string|max:255',
            'email.imap.account.password'     => 'nullable|string|max:255',
        ]);

        Event::dispatch('settings.mail_configuration.save.before');

        $this->coreConfigRepository->create(request()->all());

        Event::dispatch('settings.mail_configuration.save.after');

        session()->flash('success', trans('admin::app.settings.mail-configuration.index.save-success'));

        return redirect()->back();
    }

    /**
     * Test SMTP connection.
     */
    public function testSmtp(): JsonResponse
    {
        $this->validate(request(), [
            'host'         => 'required|string|max:255',
            'port'         => 'required|integer|min:1|max:65535',
            'encryption'   => 'nullable|string|in:tls,ssl',
            'username'     => 'required|string|max:255',
            'password'     => 'required|string|max:255',
            'from_address' => 'required|email|max:255',
            'from_name'    => 'required|string|max:255',
        ]);

        try {
            $config = [
                'transport'  => 'smtp',
                'host'       => request('host'),
                'port'       => request('port'),
                'encryption' => request('encryption'),
                'username'   => request('username'),
                'password'   => request('password'),
                'timeout'    => 10,
            ];

            Config::set('mail.mailers.test_smtp', $config);
            Config::set('mail.from.address', request('from_address'));
            Config::set('mail.from.name', request('from_name'));

            // Test connection by sending a test email
            Mail::mailer('test_smtp')->raw('This is a test email to verify SMTP configuration.', function ($message) {
                $message->to(request('from_address'))
                    ->subject('SMTP Configuration Test');
            });

            return response()->json([
                'success' => true,
                'message' => trans('admin::app.settings.mail-configuration.index.smtp-test-success'),
            ], 200);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => trans('admin::app.settings.mail-configuration.index.smtp-test-failed', [
                    'error' => $exception->getMessage(),
                ]),
            ], 400);
        }
    }

    /**
     * Test IMAP connection.
     */
    public function testImap(): JsonResponse
    {
        $this->validate(request(), [
            'host'       => 'required|string|max:255',
            'port'       => 'required|integer|min:1|max:65535',
            'encryption' => 'nullable|string|in:tls,ssl,notls',
            'username'   => 'required|string|max:255',
            'password'   => 'required|string|max:255',
        ]);

        try {
            $config = [
                'host'          => request('host'),
                'port'          => request('port'),
                'encryption'    => request('encryption', 'tls'),
                'validate_cert' => request('validate_cert', true),
                'username'      => request('username'),
                'password'      => request('password'),
            ];

            // Create IMAP connection string
            $encryption = match ($config['encryption']) {
                'ssl'   => '/ssl',
                'tls'   => '/tls',
                'notls' => '/notls',
                default => '/tls',
            };

            $validateCert = $config['validate_cert'] ? '' : '/novalidate-cert';

            $mailbox = sprintf(
                '{%s:%s/imap%s%s}INBOX',
                $config['host'],
                $config['port'],
                $encryption,
                $validateCert
            );

            // Attempt to connect to IMAP server
            $connection = @imap_open(
                $mailbox,
                $config['username'],
                $config['password'],
                OP_READONLY,
                1
            );

            if ($connection === false) {
                $error = imap_last_error() ?: 'Unable to connect to IMAP server';

                return response()->json([
                    'success' => false,
                    'message' => trans('admin::app.settings.mail-configuration.index.imap-test-failed', [
                        'error' => $error,
                    ]),
                ], 400);
            }

            imap_close($connection);

            return response()->json([
                'success' => true,
                'message' => trans('admin::app.settings.mail-configuration.index.imap-test-success'),
            ], 200);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => trans('admin::app.settings.mail-configuration.index.imap-test-failed', [
                    'error' => $exception->getMessage(),
                ]),
            ], 400);
        }
    }

    /**
     * Get SMTP configuration from database.
     */
    protected function getSmtpConfiguration(): array
    {
        return [
            'host'         => core()->getConfigData('email.smtp.account.host') ?? config('mail.mailers.smtp.host'),
            'port'         => core()->getConfigData('email.smtp.account.port') ?? config('mail.mailers.smtp.port'),
            'encryption'   => core()->getConfigData('email.smtp.account.encryption') ?? config('mail.mailers.smtp.encryption'),
            'username'     => core()->getConfigData('email.smtp.account.username') ?? config('mail.mailers.smtp.username'),
            'password'     => core()->getConfigData('email.smtp.account.password') ?? config('mail.mailers.smtp.password'),
            'from_address' => core()->getConfigData('email.smtp.account.from_address') ?? config('mail.from.address'),
            'from_name'    => core()->getConfigData('email.smtp.account.from_name') ?? config('mail.from.name'),
        ];
    }

    /**
     * Get IMAP configuration from database.
     */
    protected function getImapConfiguration(): array
    {
        return [
            'host'          => core()->getConfigData('email.imap.account.host'),
            'port'          => core()->getConfigData('email.imap.account.port'),
            'encryption'    => core()->getConfigData('email.imap.account.encryption'),
            'validate_cert' => core()->getConfigData('email.imap.account.validate_cert'),
            'username'      => core()->getConfigData('email.imap.account.username'),
            'password'      => core()->getConfigData('email.imap.account.password'),
        ];
    }
}
