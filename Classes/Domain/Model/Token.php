<?php
namespace Skar\Skfbalbums\Domain\Model;

/***
 *
 * This file is part of the "FB Albums" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2017 Stefanos Karasavvidis <sk@karasavvidis.gr>
 *
 ***/

/**
 * Token
 */
class Token extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * albumRepository
     *
     * @var \Skar\Skfbalbums\Domain\Repository\AlbumRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $albumRepository = null;


    /**
     * albumRepository
     *
     * @var \Skar\Skfbalbums\Domain\Repository\TokenRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $tokenRepository = null;


    /**
     * photoRepository
     *
     * @var \Skar\Skfbalbums\Domain\Repository\PhotoRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $photoRepository = null;




    /**
     * name
     *
     * @var string
     */
    protected $name = '';

    /**
     * Facebook Access Token
     *
     * @var string
     */
    protected $accessToken = '';


    /**
     * Facebook Page ID
     *
     * @var string
     */
    protected $pageId = '';

    /**
     * excludeAlbumIds
     *
     * @var string
     */
    protected $excludeAlbumIds = '';

    /**
     * includeAlbumIds
     *
     * @var string
     */
    protected $includeAlbumIds = '';


    /**
     * defaultdownload
     *
     * @var bool
     */
    protected $defaultdownload = false;

    /**
     * warnings
     *
     * @var array
     */
    protected $warnings = [];

    /**
     * Returns the defaultdownload
     *
     * @return bool $defaultdownload
     */
    public function getDefaultdownload()
    {
        return $this->defaultdownload;
    }

    /**
     * Sets the defaultdownload
     *
     * @param bool $defaultdownload
     * @return void
     */
    public function setDefaultdownload($defaultdownload)
    {
        $this->defaultdownload = $defaultdownload;
    }

    /**
     * Returns the boolean state of defaultdownload
     *
     * @return bool
     */
    public function isDefaultdownload()
    {
        return $this->defaultdownload;
    }


    /**
     * Returns the accessToken
     *
     * @return string $accessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Sets the accessToken
     *
     * @param string $accessToken
     * @return void
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Returns the pageId
     *
     * @return string $pageId
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * Sets the pageId
     *
     * @param string $pageId
     * @return void
     */
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
    }

    /**
     * Returns the name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name
     *
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the excludeAlbumIds
     *
     * @return string $excludeAlbumIds
     */
    public function getExcludeAlbumIds()
    {
        return $this->excludeAlbumIds;
    }

    /**
     * Sets the excludeAlbumIds
     *
     * @param string $excludeAlbumIds
     * @return void
     */
    public function setExcludeAlbumIds($excludeAlbumIds)
    {
        $this->excludeAlbumIds = $excludeAlbumIds;
    }

    /**
     * Returns the includeAlbumIds
     *
     * @return string $includeAlbumIds
     */
    public function getIncludeAlbumIds()
    {
        return $this->includeAlbumIds;
    }

    /**
     * Sets the includeAlbumIds
     *
     * @param string $includeAlbumIds
     * @return void
     */
    public function setIncludeAlbumIds($includeAlbumIds)
    {
        $this->includeAlbumIds = $includeAlbumIds;
    }


    /**
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }


    /**
     * @param int $mode
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function getAlbums($mode) {
        return $this->albumRepository->getAlbums($this, $mode, false);
    }

    /**
     * @param int $mode
     * @return int
     */
    private function getAlbumCount($mode) {
        return $this->albumRepository->getAlbums($this, $mode, true);
    }


    /**
     * @return int
     */
    public function getAlbumCountHidden() {
        return $this->getAlbumCount(\Skar\Skfbalbums\Domain\Repository\AlbumRepository::ONLY_HIDDEN);
    }

    /**
     * @return int
     */
    public function getAlbumCountNonHidden() {
        return $this->getAlbumCount(\Skar\Skfbalbums\Domain\Repository\AlbumRepository::ONLY_NONHIDDEN);
    }

    private function logError($message) {
        $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class)->getLogger(__CLASS__);

        $this->logger->error($message);
        /*
        $logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        $logger->info('Everything went fine.'. "  includeFolders is $includeFolders");
        $logger->warning('Something went awry, check your configuration!');
        $logger->error(
          'This was not a good idea',
          array(
            'foo' => 1,
            'bar' => 2,
          )
        );
        */
    }

    /**
     * checkconnection
     *
     * @return void
     */
    public function checkconnection() {

        // debug token first. Will throw exception if something is wront
        $this->debugAccessToken();

        // will throw exception if something is wrong
        $this->retrievePageAlbums(true);

        return TRUE;
    }

    private function debugAccessToken() {
        $graphAlbLink = "https://graph.facebook.com/v3.2/debug_token?input_token=".$this->getAccessToken()."&access_token=".$this->getAccessToken();
        $jsonData = @file_get_contents($graphAlbLink);
        if ($jsonData === FALSE) {
            $msg = "Error (4) retrieving expiration info for Token with TYPO3 id: ".$this->getUid();
            $this->logError($msg);
            throw new \Skar\Skfbalbums\Helper\CommunicationException($msg, 0, null, $http_response_header);
        }
        $expirationInfo = json_decode($jsonData, true, 512, JSON_BIGINT_AS_STRING);
        if (!$expirationInfo || !isset($expirationInfo['data'])) {
            $msg = "Error (5) retrieving expiration info for Token with TYPO3 id: ".$this->getUid();
            $this->logError($msg);
            throw new \Skar\Skfbalbums\Helper\CommunicationException($msg, 0, null, $http_response_header);
        }
        $expiresAt = $expirationInfo['data']['expires_at'];
        $isValid = $expirationInfo['data']['is_valid'];
        $dataAccessExpiresAt = $expirationInfo['data']['data_access_expires_at'];

        if (!$isValid) {
            $msg = "Error (6) the access token is no longer valid for Token with TYPO3 id: ".$this->getUid();
            $this->logError($msg);
            throw new \Skar\Skfbalbums\Helper\CommunicationException($msg, 0, null, $http_response_header);
        }
        if ($expiresAt && time() > $expiresAt) {
            $msg = "Error (7) the access token has expired for Token with TYPO3 id: ".$this->getUid();
            $this->logError($msg);
            throw new \Skar\Skfbalbums\Helper\CommunicationException($msg, 0, null, $http_response_header);
        }
        if (time() > $dataAccessExpiresAt) {
            $msg = "Warning (8) the access token is still valid, but the data access date has expired and must be reauthorized for Token with TYPO3 id: ".$this->getUid().". ";
            $msg .= "Depending on the way you created the access token, you may or may not face problems with it. If the sync process continues without any other error, you can ignore this warning. ";
            $this->logError($msg);
            $this->addWarning($msg);
            //throw new \Skar\Skfbalbums\Helper\CommunicationException($msg, 0, null, $http_response_header);
        }
        return TRUE;
    }

    public function addWarning($msg) {
        if (!$this->warnings) {
            $this->warnings = [];
        }
        $this->warnings[] = $msg;
    }
    public function getWarnings($clear = true) {
        $result = $this->warnings;
        if ($clear) {
            $this->warnings = null;
        }
        return $result;
    }

    /**
     * @return String
     * @throws \Skar\Skfbalbums\Helper\CommunicationException
     */
    private function retrievePageAlbums($testOnly = false) {
        $pageId = $this->getPageId();
        $fields = "id,name,description,link,cover_photo,count";
        $graphAlbLink = "https://graph.facebook.com/v3.2/{$pageId}/albums?fields={$fields}&access_token=".$this->getAccessToken();
        $jsonData = @file_get_contents($graphAlbLink);
        if ($jsonData === FALSE) {
            $msg = "Error (1) retrieving page albums for Token with TYPO3 id: ".$this->getUid();
            $this->logError($msg);
            throw new \Skar\Skfbalbums\Helper\CommunicationException($msg, 0, null, $http_response_header);
        }
        $fbAlbumObj = json_decode($jsonData, true, 512, JSON_BIGINT_AS_STRING);

        if ($testOnly) {
            if (!isset($fbAlbumObj['data']) || !is_array($fbAlbumObj['data']) || count($fbAlbumObj['data']) == 0) {
                $msg = "Connection successful but returned empty album list for Token with TYPO3 id: ".$this->getUid();
                $this->logError($msg);
                throw new \Skar\Skfbalbums\Helper\CommunicationException($msg, 0, null, null, $fbAlbumObj);
            }
            return TRUE;
        }

        if ($fbAlbumObj && isset($fbAlbumObj['data'])) {
            $fbAlbumData = $fbAlbumObj['data'];
        }
        else if ($fbAlbumObj) {
            $msg = "Error (2) retrieving page albums for Token with TYPO3 id: ".$this->getUid();
            $this->logError($msg);
            throw new \Skar\Skfbalbums\Helper\CommunicationException($msg, 0, null, null, $fbAlbumObj);
        }
        else {
            $msg = "Error (3) retrieving page albums for Token with TYPO3 id: ".$this->getUid();
            $this->logError($msg);
            throw new \Skar\Skfbalbums\Helper\CommunicationException($msg, 0, null, $http_response_header);
        }
        while ($fbAlbumObj && isset($fbAlbumObj['paging']) && isset($fbAlbumObj['paging']['next'])) {
            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump("PAGED RESULT FOR ALBUM"); 
            $jsonData = @file_get_contents($fbAlbumObj['paging']['next']);
            $fbAlbumObj = json_decode($jsonData, true, 512, JSON_BIGINT_AS_STRING);
            if ($fbAlbumObj && isset($fbAlbumObj['data'])) {
                $fbAlbumData = $fbAlbumData + $fbAlbumObj['data'];
            }
        }

        return $fbAlbumData;

    }

    private function retrieveAlbumPhotos($albumId) {
        $accessToken = $this->getAccessToken();
        // although in the api it says that name is deprecated and we should use caption instead, caption is empty and name has the correct text. So get both
        $graphPhoLink = "https://graph.facebook.com/v3.2/{$albumId}/photos?fields=id,source,images,caption,name&access_token={$accessToken}";
        $jsonData = @file_get_contents($graphPhoLink);
        $fbPhotoObj = json_decode($jsonData, true, 512, JSON_BIGINT_AS_STRING);

        if ($fbPhotoObj && isset($fbPhotoObj['data'])) {
            $fbPhotoData = $fbPhotoObj['data'];
        }
        else {
            $fbPhotoData = [];
        }
        while ($fbPhotoObj && isset($fbPhotoObj['paging']) && isset($fbPhotoObj['paging']['next'])) {
//            \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump("PAGED RESULT FOR PHOTOS"); 
            $jsonData = @file_get_contents($fbPhotoObj['paging']['next']);
            $fbPhotoObj = json_decode($jsonData, true, 512, JSON_BIGINT_AS_STRING);
            if ($fbPhotoObj && isset($fbPhotoObj['data'])) {
                $fbPhotoData = $fbPhotoData + $fbPhotoObj['data'];
            }
        }

        return $fbPhotoData;
    }



    /**
     * sync
     *
     * @return void
     */
    public function sync() {

        // debug token first. Will throw exception if something is wront
        $this->debugAccessToken();


        // TODO ? - do not sync if it was synced recently. Have a parameter to force it


        $albums = $this->retrievePageAlbums();
        if ($albums === FALSE) {
            // TODO some logging or better throw an exception?
            return FALSE;
        }
        $photos = [];
        //$excludedAlbums = [];
        $includedAlbums = [];
        foreach($albums as $album) {
            if ($this->allowedFromInclude($album['id']) && $this->allowedFromExclude($album['id'])) {
                $includedAlbums[] = $album;
                $photos[$album['id']] = $this->retrieveAlbumPhotos($album['id']);
            }
            else {
               // $excludedAlbums[] = $album;
            }
        }

        // load all existing db albums that belong to this token
        // if an existing db album is not in the includedAlbums, hide or delete it
        // if an includedAlbum is already in the db, update it and add it to albums to sync photos
        // if an includedAlbum is not in the db, insert it add it to albums to sync photos
        // sync photos 

        
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $persistenceManager = $objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManagerInterface');

        $existingAlbums = $this->albumRepository->getAlbumsByToken($this, true);

        $albumsHidden = 0;
        $albumsImported = 0;
        $albumsUpdated = 0;

        $dbAlbumsToSyncPhotos = [];

        foreach($existingAlbums as $existingAlbum) {
            $album = $this->dbAlbumExistsInFbAlbum($existingAlbum, $includedAlbums);
            if ($album === FALSE) { // an album is in the db that is not in the allowed facebook albums. So hide it
                $existingAlbum->setHidden(true);
                $this->albumRepository->update($existingAlbum);
                $albumsHidden++;
            }
        }


        foreach($includedAlbums as $includedAlbum) {
            $album = $this->fbObjExistsInDb($includedAlbum, $existingAlbums);
            $name = isset($includedAlbum['name'])?$includedAlbum['name']:'';
            $description = isset($includedAlbum['description'])?$includedAlbum['description']:'';
            $link = isset($includedAlbum['link'])?$includedAlbum['link']:'';
            $cover_photo = (isset($includedAlbum['cover_photo']) && isset($includedAlbum['cover_photo']['id'])) ?$includedAlbum['cover_photo']['id']:'';

            if ($album !== FALSE) { // update existing
                // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump('Existing album--> update'); 
                $album->setName($name);
                $album->setDescription($description);
                $album->setLink($link);
                $album->setCoverPhotoFbId($cover_photo);
                $album->setLastSynced(new \DateTime());
                $album->setHidden(false); // in case it was previously excluded
                $this->albumRepository->update($album);
                $albumsUpdated++;
                $dbAlbumsToSyncPhotos[] = $album;
                 
            }
            else {
                // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump('New album--> insert'); 

                $album = $objectManager->get('Skar\\Skfbalbums\\Domain\\Model\\Album');
                $album->setName($name);
                $album->setDescription($description);
                $album->setFacebookId($includedAlbum['id']);
                $album->setLink($link);
                $album->setCoverPhotoFbId($cover_photo);
                $album->setToken($this);
                $album->setLastSynced(new \DateTime());
                $album->setPid($this->getPid()); // needed in case called from scheduler
                if ($this->getDefaultdownload()) {
                    $album->setDownload(true);
                }
                else {
                    $album->setDownload(false);
                }
                $this->albumRepository->add($album);
                $albumsImported++;
                $dbAlbumsToSyncPhotos[] = $album;
            }
        }

        $cacheManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager');
        $cacheManager->getCache('cache_pages')->flushByTag('tx_skfbalbums_domain_model_album');

        $persistenceManager->persistAll(); // call it here to be sure that all ojects have an id in case a new album was inserted

        $synResultPhotos = array();
        foreach ($dbAlbumsToSyncPhotos as $album) {

            $synResultPhotos[] = $this->syncPhotos($album, $photos[$album->getFacebookId()]);
            $cacheManager->getCache('cache_pages')->flushByTag('tx_skfbalbums_domain_model_album_' . $album->getUid());
        } 
        
        // TODO - return also synResultPhotos for each album
        return [
            'albumsHidden' => $albumsHidden,
            'albumsImported' => $albumsImported,
            'albumsUpdated' => $albumsUpdated
        ];
    }

    private function syncPhotos(\Skar\Skfbalbums\Domain\Model\Album $album, $albumFbPhotos) {
        // load all existing db photos that belong to this db album
        // we import all photos. User can hide them (NOT DELETE!) in the BE
        // if an includedPhoto is already in the db, update it
        // if an includedPhoto is not in the db, insert it


        
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $persistenceManager = $objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManagerInterface');

        $existingPhotos = $this->photoRepository->getPhotosByAlbum($album, true);

        $photosHidden = 0;
        $photosImported = 0;
        $photosUpdated = 0;

        foreach($existingPhotos as $existingPhoto) {
            $photo = $this->dbPhotosExistsInFbPhotos($existingPhoto, $albumFbPhotos);
            if ($photo === FALSE) { // a photo exists in the db that is not in the photos retrieved from facebook for the current album. So delete it
                $existingPhoto->setAlbum(null);
                $this->photoRepository->update($existingPhoto);
                $this->photoRepository->remove($existingPhoto);
                $photosHidden++;
            }
        }

        foreach($albumFbPhotos as $albumFbPhoto) {
            $photo = $this->fbObjExistsInDb($albumFbPhoto, $existingPhotos);
            if ($photo !== FALSE) { // update existing
                //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump('Existing photo--> update'); 

                if ( isset($albumFbPhoto['images'])) {
                    $photo->setImages(json_encode($albumFbPhoto['images']));

                    // name is supposed to be deprecated by FB API and we should use caption. But nevertheless, only name is returend. So use both
                    $caption = isset($albumFbPhoto['caption'])?
                        $albumFbPhoto['caption']:
                        isset($albumFbPhoto['name'])?$albumFbPhoto['name']:''
                        ;

                    $photo->setCaption($caption);

                    $this->photoRepository->update($photo);
                    $photosUpdated++;
                }             
            }
            else {
                //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump('New photo--> insert'); 

                if (isset($albumFbPhoto['id']) && isset($albumFbPhoto['images'])) {
                    $photo = $objectManager->get('Skar\\Skfbalbums\\Domain\\Model\\Photo');
                    $photo->setFacebookId($albumFbPhoto['id']);
                    $photo->setImages(json_encode($albumFbPhoto['images']));

                    // name is supposed to be deprecated by FB API and we should use caption. But nevertheless, only name is returend. So use both
                    $caption = isset($albumFbPhoto['caption'])?
                        $albumFbPhoto['caption']:
                        isset($albumFbPhoto['name'])?$albumFbPhoto['name']:''
                        ;

                    $photo->setCaption($caption);
                    $photo->setAlbum($album);
                    $photo->setPid($this->getPid()); // needed in case called from scheduler
                    $this->photoRepository->add($photo);
                    $photosImported++;
                }
            }
        }
        $persistenceManager->persistAll(); // call it here to be sure that all ojects have an id in case a new album was inserted
        
        return [
            'photosHidden' => $photosHidden,
            'photosImported' => $photosImported,
            'photosUpdated' => $photosUpdated
        ];
    }


    private function dbPhotosExistsInFbPhotos( \Skar\Skfbalbums\Domain\Model\Photo $photo, $albumFbPhotos) {
        if (!$albumFbPhotos) {
            return FALSE;
        }

        foreach($albumFbPhotos as $facebookPhoto) {
            if ($photo->getFacebookId() == $facebookPhoto['id']) {
                return $photo;
            }
        }
        return FALSE;
    }

    private function dbAlbumExistsInFbAlbum( \Skar\Skfbalbums\Domain\Model\Album $album, $includedFbAlbums) {
        if (!$includedFbAlbums || !$album) { // checking !$album makes no sense
            return FALSE;
        }

        foreach($includedFbAlbums as $includedFbAlbum) {
            if ($album->getFacebookId() == $includedFbAlbum['id']) {
                return $album;
            }
        }
        return FALSE;
    }

    private function fbObjExistsInDb($fbObj, $existingDbItems) {
        if (!$existingDbItems || !$fbObj || !isset($fbObj['id'])) {
            return FALSE;
        }

        foreach($existingDbItems as $existingDbItem) {
            if ($existingDbItem->getFacebookId() == $fbObj['id']) {
                return $existingDbItem;
            }
        }
        return FALSE;
    }

    private function allowedFromInclude($albumId) {
        if (!$albumId) {
            return FALSE;
        }

        // if includeAlbumIds is empty, allow all
        if (!$this->includeAlbumIds) {
            return true;
        }

        $allowedIds = explode(',', $this->includeAlbumIds);
        if (in_array($albumId, $allowedIds)) {
            return true;
        }

        return FALSE;
    }

    private function allowedFromExclude($albumId) {
        if (!$albumId) {
            return FALSE;
        }

        // if excludeAlbumIds is empty, allow all
        if (!$this->excludeAlbumIds) {
            return true;
        }

        $excludedIds = explode(',', $this->excludeAlbumIds);
        if (in_array($albumId, $excludedIds)) {
            return FALSE;
        }

        return true;
    }
}
