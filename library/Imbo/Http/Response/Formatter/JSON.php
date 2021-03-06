<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace Imbo\Http\Response\Formatter;

use Imbo\Model,
    stdClass;

/**
 * JSON formatter
 *
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @package Response\Formatters
 */
class JSON extends Formatter implements FormatterInterface {
    /**
     * {@inheritdoc}
     */
    public function getContentType() {
        return 'application/json';
    }

    /**
     * {@inheritdoc}
     */
    public function formatError(Model\Error $model) {
        $data = array(
            'error' => array(
                'code' => $model->getHttpCode(),
                'message' => $model->getErrorMessage(),
                'date' => $this->dateFormatter->formatDate($model->getDate()),
                'imboErrorCode' => $model->getImboErrorCode(),
            ),
        );

        if ($imageIdentifier = $model->getImageIdentifier()) {
            $data['imageIdentifier'] = $imageIdentifier;
        }

        return $this->encode($data);
    }

    /**
     * {@inheritdoc}
     */
    public function formatStatus(Model\Status $model) {
        return $this->encode(array(
            'date' => $this->dateFormatter->formatDate($model->getDate()),
            'database' => $model->getDatabaseStatus(),
            'storage' => $model->getStorageStatus(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function formatUser(Model\User $model) {
        return $this->encode(array(
            'publicKey' => $model->getPublicKey(),
            'numImages' => $model->getNumImages(),
            'lastModified' => $this->dateFormatter->formatDate($model->getLastModified()),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function formatImages(Model\Images $model) {
        $images = $model->getImages();
        $data = array();

        // Fields to display
        if ($fields = $model->getFields()) {
            $fields = array_fill_keys($fields, 1);
        }

        foreach ($images as $image) {
            $entry = array(
                'added' => $this->dateFormatter->formatDate($image->getAddedDate()),
                'updated' => $this->dateFormatter->formatDate($image->getUpdatedDate()),
                'checksum' => $image->getChecksum(),
                'extension' => $image->getExtension(),
                'size' => $image->getFilesize(),
                'width' => $image->getWidth(),
                'height' => $image->getHeight(),
                'mime' => $image->getMimeType(),
                'imageIdentifier' => $image->getImageIdentifier(),
                'publicKey' => $image->getPublicKey(),
            );

            // Add metadata if the field is to be displayed
            if (empty($fields) || isset($fields['metadata'])) {
                $metadata = $image->getMetadata();

                if (is_array($metadata)) {
                    if (empty($metadata)) {
                        $metadata = new stdClass();
                    }

                    $entry['metadata'] = $metadata;
                }
            }

            // Remove elements that should not be displayed
            if (!empty($fields)) {
                foreach (array_keys($entry) as $key) {
                    if (!isset($fields[$key])) {
                        unset($entry[$key]);
                    }
                }
            }

            $data[] = $entry;
        }

        return $this->encode(array(
            'search' => array(
                'hits' => $model->getHits(),
                'page' => $model->getPage(),
                'limit' => $model->getLimit(),
                'count' => $model->getCount(),
            ),
            'images' => $data,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function formatMetadata(Model\Metadata $model) {
        return $this->encode($model->getData() ?: new stdClass());
    }

    /**
     * {@inheritdoc}
     */
    public function formatArrayModel(Model\ArrayModel $model) {
        return $this->encode($model->getData() ?: new stdClass());
    }

    /**
     * {@inheritdoc}
     */
    public function formatListModel(Model\ListModel $model) {
        return $this->encode(array($model->getContainer() => $model->getList()));
    }

    /**
     * {@inheritdoc}
     */
    public function formatStats(Model\Stats $model) {
        $data = array(
            'users' => $model->getUsers(),
            'total' => array(
                'numImages' => $model->getNumImages(),
                'numUsers' => $model->getNumUsers(),
                'numBytes' => $model->getNumBytes(),
            ),
            'custom' => $model->getCustomStats() ?: new stdClass(),
        );

        return $this->encode($data);
    }

    /**
     * JSON encode an array
     *
     * @param mixed $data The data to encode
     * @return string
     */
    private function encode($data) {
        return json_encode($data);
    }
}
