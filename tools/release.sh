#!/bin/bash

# Ensure the script stops on the first error encountered
set -e

# Run composer install without dev dependencies
echo "Running Composer install..."
composer install --no-dev

# Build the assets using npm
echo "Building assets..."
npm run build

# Package the plugin into a zip file
echo "Creating plugin zip..."
npm run plugin-zip

echo "Operations completed successfully."
