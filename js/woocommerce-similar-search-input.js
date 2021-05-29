//const searchBar = document.querySelector('.woocommsearch');
//const foundMatchesContainer = document.querySelector('.found-matches');
//const ajaxUrl = my_script_vars.ajaxurl;

function WoocommerceSimilarSearchInput(){

    this.searchBar = document.querySelector('.woocommsearch');
    this.foundMatchesContainer = document.querySelector('.found-matches');
    this.ajaxUrl = my_script_vars.ajaxurl;
    this.spinner = document.getElementById('loading');
    this.magnifier = document.querySelector('.woocomm-sim-search-magnifier');
    this.loading = false;
    this.debouncer = null;

    this.searchBar.addEventListener('input', (e) => {
      clearTimeout(this.debouncer);
      this.debouncer = setTimeout(() => this.similarSearchInputDetected(e), 500)
    } );
//function(e){this.similarSearchInputDetected(e)}.bind(this)
}

WoocommerceSimilarSearchInput.prototype = {
    similarSearchInputDetected : function(e){
        this.setLoadingStatus(true);
        if(e.target.value == ""){
          this.setLoadingStatus(false);
            this.foundMatchesContainer.style.display = "none";
            return;
          }

          jQuery.ajax({ 
              context: this,
              url: this.ajaxUrl,
              type: 'post',
              data: { action: 'data_fetch', keyword: this.searchBar.value },
              success: function(data) {

                const products = JSON.parse(data);
                if(products.length < 1){

                  this.setLoadingStatus(false);
                  this.displayNoResults();
                  return;
                }
                this.setLoadingStatus(false);
                this.parseAndDisplay(JSON.parse(data));
              }
          });
    },

    displayNoResults : function(){
        this.foundMatchesContainer.innerHTML = "";

        const outerDiv = document.createElement('div');
        outerDiv.innerText = "No results found..";
        this.foundMatchesContainer.appendChild(outerDiv);
        this.foundMatchesContainer.style.display = "block";
    },

    parseAndDisplay : function(products){
        this.foundMatchesContainer.style.display = "block";
      
        this.foundMatchesContainer.innerHTML = "";

        products.sort(function(a, b){return b.similarity - a.similarity});
      
        products.forEach(prod => {
          let productOuterDiv = document.createElement('div');
          let productLink = document.createElement('a');
      
          productLink.href = prod.url;
      
          productLink.innerText = prod.name;
          productOuterDiv.classList.add('found-product');
          this.foundMatchesContainer.appendChild(productOuterDiv)
          productOuterDiv.appendChild(productLink);
        })
    },
    setLoadingStatus : function(truth){
      if(truth){
        this.loading = true;
        this.spinner.style.display = "inline-block";
        this.magnifier.style.display = "none";
      }
      else{
        this.loading = false;
        this.spinner.style.display = "none";
        this.magnifier.style.display = "inline-block";
      }
    }
}

new WoocommerceSimilarSearchInput();
