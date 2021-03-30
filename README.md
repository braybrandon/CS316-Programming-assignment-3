# CS316-Programming-assignment-3

  bank-3.php is a bank account file that utilizes front-end jquery with ajax to do all of its 
  back-end calls. Whenever the user loads the page the jquery does an ajax call to the server
  requesting for the bank account information. The bank account information is stored via MySQL
  in a table called bankAccount. The table has 3 columns, an id column, a checking column, and a 
  savings column. The checking column holds the amount that's in the persons checking account. The
  savings column holds the amount that's in the persons savings account. After the query for the 
  account information the server encodes the data into JSON and sends it back to the browser. The
  browser then decodes the information and displays the account information to the user. The user 
  has 4 options they can either deposit into checking, deposit into savings, transfer from
  checking to savings, or transfer from savings into checking. When the user decides to enter
  information in any 4 of these input fields the browser verifies the information is a valid 
  numerical number and that it is not negative as well as overdraw the account. Then the browser 
  will do a post call to update the database without reloading the browser. Then the browser 
  request for the new account information to update the table on the webpage. There is also a 
  reset button for testing purposes that will reset the database back to the original bank account
  values of 100 in checking and 1000 in savings. The reset would not be used during deployment.
 
