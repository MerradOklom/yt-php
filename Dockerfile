# Start with an official PHP image with Apache
FROM php:8.1-apache

# Install yt-dlp and required dependencies
RUN apt-get update && \
    apt-get install -y python3 python3-pip ffmpeg && \
    pip3 install yt-dlp && \
    rm -rf /var/lib/apt/lists/*

# Copy index.php to the web root
COPY index.php /var/www/html/

# Create the log file and set permissions
RUN touch /var/www/html/yt-dlp.log && chmod 666 /var/www/html/yt-dlp.log

# Set permissions on the web root
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Expose port 80 for web traffic
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
