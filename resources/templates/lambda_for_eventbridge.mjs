import * as https from 'https';


export const handler = async(event) => {
  
  var post_data = JSON.stringify(event);

  // An object of options to indicate where to post to
  var post_options = {
      host: 'prasso.io',
      port: '443',
      path: '/api/livestream_activity',
      method: 'POST',
      headers: {
          'Content-Type': 'application/json',
          'Content-Length': Buffer.byteLength(post_data)
    }
  };

  function get(options) {
  return new Promise(((resolve, reject) => {
    //disabled due to 168 start/end emails during one session
    return
    const request = https.request(options, (response) => {
      response.setEncoding('utf8');
      let returnData = '';

      if (response.statusCode < 200 || response.statusCode >= 300) {
        return reject(new Error(`${response.statusCode}: ${response.req.getHeader('host')} ${response.req.path}`));
      }

      response.on('data', (chunk) => {
        returnData += chunk;
      });

      response.on('end', () => {
        resolve((returnData));
      });

      response.on('error', (error) => {
        reject(error);
      });
      
    });
    
    request.write(post_data)
    request.end();
    
  }));
}

let response = await get(post_options);
return response
}