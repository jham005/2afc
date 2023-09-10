# 2afc
A 2-alternative force choice experiment framework.

In a 2afc experiment, participants are presented with a sequence of image pairs.
For each image pair, they are asked to select one, based on some criteria.

# Setup
For the minimal set up, we need
 * an “experimental folder” containing n folders, where each folder has two images in it
 * a “practice folder” containing m folders, where each folder has two images in it
 * an “instructions folder” containing one text file with instruction text in it

Actually, for flexibility, we could have folders labelled
 * Image-pairs1 
 * Image-pairs2 
 * Image-pairs3 
 * ...
 * Image-pairsn

And they can be used in the given order. (so image-pairs1 will usually hold the practice images, and image-pairs3 might be a different type of image

The procedure:
 * randomly pick folders from the practice folder (until they are all complete)
 * then randomly pick folders from the experimental folder (until they are all complete)
 * for each folder show the two images side by side (ordering on the page being random), show the instruction text
 * the user will click one of the images - record the pair, the image clicked and the time
 * after every 15 trials, allow a break (showing a page saying 'Take a break, press next to continue')

Also including (as before) 
 * Consent 
 * Tutorial  
 * Post-task questionnaire 

Configuration: upload from a ZIP file

```
consent.html
tutorial.html
questionnare.html
practice/
  $[image}.html
  ${image}-a.{png,jpg}
  ${image}-b.{png.jpg}
  ...
experiment/
  $[image}.html
  ${image}-a.{png,jpg}
  ${image}-b.{png.jpg}
  ...
users.dat (rw)
results.dat (rw)
survey.dat (rw)
```
