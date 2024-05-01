const fs = require('fs');

// Function to display all files in a directory
function displayFilesInDirectory(directoryPath) {
    fs.readdir(directoryPath, (err, files) => {
        if (err) {
            console.error('Error reading directory:', err);
            return;
        }

        console.log('Files in directory:', directoryPath);
        files.forEach(file => {
            console.log(file);
        });
    });
}

// Example: Display files in the current directory
displayFilesInDirectory('./literature');
