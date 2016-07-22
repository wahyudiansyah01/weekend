var app = angular.module("MyApp", []);




app.controller("feedrss", function($scope, $http) {
    $scope.data = [];
    
 $http.get('regex.php').success(function(response){ //make a get request to mock json file.
            $scope.data = response[0]; 
            $scope.domain = response[2];
            console.log($scope.data);
            if($scope.data.produk.length){
                $scope.adadiskon = response[0].adadiskon;
            }
            else{
                $scope.adadiskon = "";
            }
         })
    .error(function (data, status) {
        //do whatever you want with the error
    });
    
    $scope.subscribe = function(){
         $http({
  method  : 'POST',
  url     : 'curl.php',
  data    : $.param({
      email: $scope.email,
      name: ""
  }), // pass in data as strings
  headers : { 'Content-Type': 'application/x-www-form-urlencoded' }  // set the headers so angular passing info as form data (not request payload)
 })
  .success(function(data) {
    if(/The Email field must contain a valid email address./.test(data)){
            console.log("The Email field must contain a valid email address.");
     $scope.error = "The Email field must contain a valid email address.";
            $('.error').fadeIn(400).delay(3000).fadeOut(400);
        }
        else if(/Thank you for subscribing to our newsletter./.test(data)){
            console.log("Thank you for subscribing to our newsletter.");
             $scope.error = "Thank you for subscribing to our newsletter.";
            $('.error').fadeIn(400).delay(3000).fadeOut(400);
              $scope.email = "";
        }
        else if(/This email has already been subscribed./.test(data)){
            console.log("This email has already been subscribed.");
            $scope.error = "This email has already been subscribed.";
            $('.error').fadeIn(400).delay(3000).fadeOut(400);
        }
        else if(/The Email field is required./.test(data)){
            console.log("The Email field is required.");
            $scope.error = "The Email field is required.";
            $('.error').fadeIn(400).delay(3000).fadeOut(400);
        }
  });};
});