/**
 * Sets client side properties of the website
 *
 * Properties marked with a * are enabled no matter what
 */
gOptions = {
    enabled: false,     // Enables or disables the config file
    name: 'Smith',       // Sets the name of the website from FamShare to SmithShare here for example (if enabled)

    // Dropzone values -- used by the file upload API (dropzone) -- more config options are in js/api/dropzone.js
    maxFilesize: 100500000,     //*, In mega byte
    maxFiles: 100               //*
};