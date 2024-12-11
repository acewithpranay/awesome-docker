#!/bin/bash

# Color codes
CYANBG='\033[0;96m'
GREEN='\033[0;92m'
YELLOW='\033[0;33m'
ORANGE='\033[38;5;208m'
NC='\033[0m' # No Color

# Helper function to fetch the latest release version
function getReleaseVersion() {
  curl --silent "https://api.github.com/repos/$1/releases/latest" |
    grep '"tag_name":' |
    sed -E 's/.*"([^"]+)".*/\1/'
}

# Non-interactive WordPress configuration
function installAw2(){
    echo -e "${CYANBG}Starting WordPress configuration...${NC}"

    # Redis configuration
    wp config set REDIS_DATABASE_GLOBAL_CACHE ${REDIS_DATABASE_GLOBAL_CACHE} --add=true --type=constant --allow-root
    wp config set REDIS_DATABASE_SESSION_CACHE ${REDIS_DATABASE_SESSION_CACHE} --add=true --type=constant --allow-root
    wp config set REDIS_HOST ${REDIS_HOST:-127.0.0.1} --add=true --type=constant --allow-root
    wp config set REDIS_PORT ${REDIS_PORT:-6379} --add=true --type=constant --allow-root

    # Awesome-Enterprise Path and URLs
    wp config set AWESOME_PATH /var/www/awesome-enterprise --add=true --type=constant --allow-root
    wp config set SITE_URL "${SITE_URL}" --add=true --type=constant --allow-root
    wp config set HOME_URL "${HOME_URL}" --add=true --type=constant --allow-root

    # Post revisions
    wp config set WP_POST_REVISIONS ${WP_POST_REVISIONS:-100} --allow-root

    echo -e "${GREEN}Installing Monomyth Enterprise latest version...${NC}"
    wp theme install https://github.com/WPoets/monomyth-enterprise/archive/master.zip --activate --allow-root --quiet

    echo -e "${GREEN}Installing Awesome Enterprise WP Plugin...${NC}"
    aw2_plugin_version=$(getReleaseVersion 'WPoets/awesome-enterprise-wp')
    wp plugin install https://github.com/WPoets/awesome-enterprise-wp/archive/${aw2_plugin_version}.zip --activate --allow-root --quiet

    # Additional plugins
    wp plugin install advanced-custom-fields --activate --allow-root --quiet
    wp plugin install custom-post-type-ui --activate --allow-root --quiet
    wp plugin install wordpress-importer --activate --allow-root --quiet
    wp plugin install classic-editor --allow-root --quiet
    wp plugin install fluent-smtp --activate --allow-root --quiet
    wp plugin install google-apps-login --allow-root --quiet

    # Set permalink structure
    wp rewrite structure '/%postname%/' --allow-root --quiet

    echo -e "${GREEN}Importing Basic Apps and Core Services...${NC}"
    wget -O /tmp/core.xml https://raw.githubusercontent.com/WPoets/aw-setup/master/code/core.xml
    wget -O /tmp/basic-apps.xml https://raw.githubusercontent.com/WPoets/aw-setup/master/code/basic-apps.xml
    wp import /tmp/basic-apps.xml --authors=skip --allow-root --quiet
    wp import /tmp/core.xml --authors=skip --allow-root --quiet

    echo -e "${ORANGE}Configuration completed successfully!${NC}"
}

# Main script logic
SITE=$1
if [ -z "$SITE" ]; then
    echo -e "${YELLOW}Usage: $0 <site-domain>${NC}"
    exit 1
fi

echo "Checking WordOps..."
if ! wo; then
    echo -e "${YELLOW}WordOps is not installed!${NC}"
    exit 1
fi

# Navigate to the site's directory and configure
if ! wo site info $SITE > /dev/null 2>&1; then
    echo -e "${YELLOW}Site $SITE does not exist!${NC}"
    exit 1
fi
