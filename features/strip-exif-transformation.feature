Feature: Imbo can strip EXIF data from images
    In order to fetch images with all EXIF-data removed
    As an HTTP Client
    I can use the stripExif transformation

    Background:
        Given "tests/Fixtures/exif-logo.jpg" exists in Imbo

    Scenario: Use the stripExif transformation
        Given I use "publickey" and "privatekey" for public and private keys
        And I specify "strip" as transformation
        And I include an access token in the query
        When I request "/users/publickey/images/753e11e00522ff1e95600d8f91c74e8e.jpg"
        Then I should get a response with "200 OK"
        And the "Content-Type" response header is "image/jpeg"
        And the image should not have any "exif" properties
