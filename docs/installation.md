# Installing the module

1. Clone (via git) or download the module to your server
2. Copy the modules `ant_api` directy into the root folder of Magento this can be done by using a FTP client or running the command on a linux based machine:
```
yes | cp ant_api/. /magento_root/ -Rf
```
3. Flush your magento cache, under System > Cache Management > Flush Magento Cache
4. Logout and Log back into your Magento, if this is a fresh install you should see the Ant tab displayed in the UI.
5. Confirm the API is active by going to this link: `https://<your magento store url>/api/rest/ant/products?limit=20&page=1`. If you see a 403 Access Denied it means you are good to go! If you see a 404 html page, it means your nginx or apache config is incorrect, [follow this totorial](https://anthq.zendesk.com/hc/en-us/articles/360000202156-Troubleshooting-Magento-Installations) to fix this. After you have fixed it, retry this step to confirm everything works before moving on. Ant will not function properly if this is not working.
6. [Install Magento on your Ant account](https://anthq.zendesk.com/hc/en-us/articles/222096147-Installing-Magento-1-9-x)
