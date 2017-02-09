#!/bin/bash

cd `dirname $0`

cp /home/reviewuser/CustomerReview.csv.gz .

gzip -f -d ./CustomerReview.csv.gz

php readCustomerReview.php

php postReviewDataToDev.php