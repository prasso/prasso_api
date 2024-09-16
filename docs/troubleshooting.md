Certainly! Here is the provided text formatted in Markdown:

```markdown
### How to Debug iOS and Send Files

1. **Run Using Local IP Address Instead of `localhost`**

   To debug iOS, use your local IP address instead of `localhost`.

2. **Get Your IP**

   Run the following command in your terminal to get your local IP address:

   ```bash
   ipconfig getifaddr en0
   ```

3. **Update the Sites Table**

   Put your local IP address into the sites table so that the API recognizes it. Insert the IP address into the `host` column of the sites table.

   Example `host` values in the sites table:

   ```
   192.168.12.204:8000,localhost:8000,localhost:8001,
   ```

4. **Run the Server to Allow Connections from All IP Addresses**

   Execute the following command to run the server and allow connections from all IP addresses:

   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

   This will serve your application on `0.0.0.0:8000`, making it accessible from any IP address.
```

Feel free to adjust the formatting as needed.