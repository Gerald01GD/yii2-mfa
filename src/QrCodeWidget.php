<?php
/**
 * @link https://github.com/vuongxuongminh/yii2-mfa
 * @copyright Copyright (c) 2019 Vuong Xuong Minh
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace vxm\mfa;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use yii\base\InvalidCallException;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Class QrCodeWidget provide a qr code for authenticator like google authenticator of current logged in user.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.0
 */
class QrCodeWidget extends Widget
{

    use EnsureUserBehaviorAttachedTrait;

    /**
     * @var array HTML img tag attributes.
     */
    public $options = [];

    /**
     * @var string an issuer will show in authenticator application. If not set an application name will be use to set by default.
     */
    public $issuer;

    /**
     * @var string a label will show in authenticator application.
     */
    public $label;

    /**
     * @var string a image will show in authenticator application.
     */
    public $image;

    /**
     * @var integer the size of the QR code.
     */
    public $size = 300;

    /**
     * @var integer the margin of the QR code.
     */
    public $margin = 10;

    /**
     * @var \Endroid\QrCode\Color\Color the foreground color of the QR code.
     */
    public $foregroundColor = null;

    /**
     * @var \Endroid\QrCode\Color\Color the background color of the QR code.
     */
    public $backgroundColor = null;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->ensureUserBehaviorAttached();

        if ($this->foregroundColor === null) {
            $this->foregroundColor = new Color(0, 0, 0);
        }

        if ($this->backgroundColor === null) {
            $this->backgroundColor = new Color(255, 255, 255, 0);
        }

        parent::init();
    }

    /**
     * @inheritDoc
     * @throws InvalidCallException
     */
    public function run()
    {
        $params = [];

        if ($this->issuer) {
            $params['issuer'] = $this->issuer;
        }

        if ($this->label) {
            $params['label'] = $this->label;
        }

        if ($this->image) {
            $params['image'] = $this->image;
        }

        $uri = $this->user->getQrCodeUri($params);

        if ($uri) {
            $qrCode = QrCode::create($uri)
                ->setSize($this->size)
                ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
                ->setEncoding(new Encoding('UTF-8'))
                ->setMargin($this->margin)
                ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
                ->setForegroundColor($this->foregroundColor)
                ->setBackgroundColor($this->backgroundColor);

            $pngWriter = new PngWriter();

            $result = $pngWriter->write($qrCode);

            $qrCodeUri = $result->getDataUri();

            return Html::img($qrCodeUri, $this->options);
        } else {
            throw new InvalidCallException('Current user is guest, can not render qr code!');
        }
    }

}
