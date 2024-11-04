# Start with an official PHP image with Apache
FROM php:8.1-apache

# Install required dependencies
RUN apt-get update && \
    apt-get install -y python3 python3-venv ffmpeg && \
    rm -rf /var/lib/apt/lists/*

# Create a virtual environment and install yt-dlp
RUN python3 -m venv /opt/yt-dlp-venv && \
    /opt/yt-dlp-venv/bin/pip install yt-dlp

# Copy index.php to the web root
COPY index.php /var/www/html/

# Create the log file and set permissions
RUN touch /var/www/html/yt-dlp.log && chmod 666 /var/www/html/yt-dlp.log

# Set permissions on the web root
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Expose port 80 for web traffic
EXPOSE 80

# Add the virtual environment's bin directory to PATH in Apache's environment
ENV PATH="/opt/yt-dlp-venv/bin:$PATH"

# Start Apache in the foreground
CMD ["apache2-foreground"]
