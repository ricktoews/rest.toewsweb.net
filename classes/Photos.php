<?php
class Photos {
  public function __construct() {
  }

  public function test() {
    use Google\Auth\Credentials\UserRefreshCredentials;
    use Google\Photos\Library\V1\PhotosLibraryClient;
    use Google\Photos\Library\V1\PhotosLibraryResourceFactory;

    try {
        // Use the OAuth flow provided by the Google API Client Auth library
        // to authenticate users. See the file /src/common/common.php in the samples for a complete
        // authentication example.
        $authCredentials = new UserRefreshCredentials( /* Add your scope, client secret and refresh token here */ );

        // Set up the Photos Library Client that interacts with the API
        $photosLibraryClient = new PhotosLibraryClient(['credentials' => $authCredentials]);
    
        // Create a new Album object with at title
        $newAlbum = PhotosLibraryResourceFactory::album("My Album");
    
        // Make the call to the Library API to create the new album
        $createdAlbum = $photosLibraryClient->createAlbum($newAlbum);
    
        // The creation call returns the ID of the new album
        $albumId = $createdAlbum->getId();
    } catch (\Google\ApiCore\ApiException $exception) {
        // Error during album creation
    } catch (\Google\ApiCore\ValidationException $e) {
        // Error during client creation
        echo $exception;
    }
  }
}
