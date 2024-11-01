<?php

namespace Tussendoor\Billink\Controllers;

use Exception;
use Throwable;
use Tussendoor\Billink\App;
use Tussendoor\Billink\Helpers\Log;
use Tussendoor\Http\RequestException;
use Tussendoor\Billink\Helpers\Notice;
use Tussendoor\Billink\Exceptions\UserError;
use Tussendoor\Billink\Exceptions\InternalError;
use Tussendoor\Billink\Exceptions\InvalidResponse;
use Tussendoor\Billink\Exceptions\ValidationError;

class ErrorController
{
    /**
     * Method for logging some sort of error when processing an Order
     * @param  Exception $error
     * @param  int       $orderId
     * @return bool
     */
    public function orderProcessingFailed($error, $orderId)
    {
        $logData = array_merge(['order' => $orderId], $this->parseErrorForLog($error));

        if ($error instanceof UserError) {
            return Log::info('Payment processing failed.', $logData);
        }

        if ($error instanceof InternalError) {
            return Log::warning('An error occured while processing an order.', $logData);
        }

        if ($error instanceof Exception || $error instanceof Throwable) {
            return Log::error('An unknown error occured while processing an order.', $logData);
        }

        return Log::error('An error was encountered while processing an order, but no known error was supplied.', $logData);
    }

    /**
     * Method for logging some sort of error when crediting an order failed.
     * @param  Exception $error
     * @param  int       $orderId
     * @return bool
     */
    public function orderCreditFailed($error, $orderId)
    {
        $notice = (new Notice())->setSuccess(false);
        $logData = array_merge(['order' => $orderId], $this->parseErrorForLog($error));

        if ($error instanceof ValidationError) {
            return $notice->setMessage(sprintf(
                __("A validation error occured. Error(s): %s", 'woocommerce-gateway-billink'),
                $error->getMessage()
            ))->create();
        }

        if ($error instanceof RequestException) {
            Log::error('A HTTP error occured when sending the credit request.', $logData);

            return $notice->setMessage(sprintf(
                __("A connection error occured while sending the credit request. Error(s): %s", 'woocommerce-gateway-billink'),
                implode(', ', $error->getErrors())
            ))->create();
        }

        if ($error instanceof InvalidResponse) {
            Log::warning('An invalid response was received from Billink when sending the credit request.', $logData);

            return $notice->setMessage(sprintf(
                __("Billink returned an error while trying to credit an order. Error: %s", 'woocommerce-gateway-billink'),
                $error->getMessage()
            ))->create();
        }

        if ($error instanceof Exception || $error instanceof Throwable) {
            Log::error('An unknown error occured while trying to credit an order in Billink.', $logData);

            return $notice->setMessage(__("An unknown error occured while trying to credit an order in Billink. Consult the logs for a detailed error report.", 'woocommerce-gateway-billink'))->create();
        }

        return Log::error('An error was encountered while processing an order, but no known error was supplied.', $logData);
    }

    /**
     * Method for logging some sort of error when starting the workflow for an order failed.
     * @param  Exception $error
     * @param  int       $orderId
     * @return bool
     */
    public function orderStartWorkflowFailed($error, $orderId)
    {
        $notice = (new Notice())->setSuccess(false);
        $logData = array_merge(['order' => $orderId], $this->parseErrorForLog($error));

        if ($error instanceof RequestException) {
            Log::error('A HTTP error occured when sending the startworkflow request.', $logData);

            return $notice->setMessage(sprintf(
                __("A connection error occured while sending the start workflow request. Error(s): %s", 'woocommerce-gateway-billink'),
                implode(', ', $error->getErrors())
            ))->create();
        }

        if ($error instanceof InvalidResponse) {
            Log::warning('An invalid response was received from Billink when sending the startworkflow request.', $logData);

            return $notice->setMessage(sprintf(
                __("Billink returned an error while trying to start the workflow. Error: %s", 'woocommerce-gateway-billink'),
                $error->getMessage()
            ))->create();
        }

        if ($error instanceof Exception || $error instanceof Throwable) {
            Log::error('An unknown error occured while starting the workflow for an order.', $logData);

            return $notice->setMessage(__("An unkown error occured while trying to start the workflow. Consult the logs for a detailed error report.", 'woocommerce-gateway-billink'))->create();
        }

        return Log::error('An error was encountered while starting the workflow for an order, but no known error was supplied.', $logData);
    }

    /**
     * Parse a given error (Exception) for the log. Injects the backtrace
     * if the plugin is set to debug mode.
     * @param  Exception $error
     * @return array
     */
    protected function parseErrorForLog($error)
    {
        $data = ['error' => $error->getMessage(), 'errorType' => get_class($error)];

        if (App::get('debug')) {
            $data['stacktrace'] = $error->getTraceAsString();
        }

        return $data;
    }
}
