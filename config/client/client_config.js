/**
 * Sets client side properties of the website
 *
 * Properties marked with a * are enabled no matter what
 */
gOptions = {
    enabled: false,     // Enables or disables the config file
    name: 'Smith',       // Sets the name of the website from FamShare to SmithShare here for example (if enabled)

    // Dropzone values -- used by the file upload API (dropzone) -- more config options are in js/api/dropzone.js
    maxFilesize: 10000,         //*, In mega byte
    maxFiles: 1000,             //*
    parallelUploads: 10,        //*
    resumableUpload: true       //*, Set to true if resumable uploads should be used. If your browser does not support
                                // it, you can disable the resumable upload by setting it to false.
};