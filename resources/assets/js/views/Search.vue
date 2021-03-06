<template>
  <div class="row search-row" v-cloak>

    <div class="load-container load7 fullscreen" v-if="isProcessing">
      <div class="loader">Searching...</div>
    </div>

    <nav v-bind:class="sidebarClass" class="sidebar d-none d-md-block">

      <div v-if="searchUser">
        <user-profile-card v-if="searchUser"
                           :searchUser="searchUser"></user-profile-card>
        <div class="form-group mt-4">
          <label for="collectionsSelectSelect">Recipes, Materials & Bookmarks:</label>
          <b-form-select
              id="collectionsSelectSelect"
              size="sm"
              v-model="selectedSearchTypeOrCollection"
              :options="collectionsSelect"
              value-field="id"
              text-field="name"
              @input="selectSearchType">
            <template slot="first">
              <option value="recipes">{{ isViewingSelfSelectText }} Recipes</option>
              <option value="materials">{{ isViewingSelfSelectText }} Materials</option>
              <option value="analyses">{{ isViewingSelfSelectText }} Analyses</option>
              <option value="images">{{ isViewingSelfSelectText }} Images</option>
              <option value="null" disabled>-- {{ isViewingSelfSelectText }} Bookmarks: --</option>
            </template>
          </b-form-select>
        </div>
      </div>

      <b-btn
              v-b-tooltip.hover
              :title="expandbuttonTooltip"
              size="sm"
              type="reset"
              variant="secondary"
              @click.prevent="toggleExpandMap"
              class="expand-button"
              v-html="expandButtonText">
      </b-btn>
      <div >
        <div id="umf-d3-chart-container">
          <umf-d3-chart
              :recipeData="searchItems"
              :width="chartWidth"
              :height="chartHeight"
              :margin="chartMargin"
              :axisLabelFontSize="'0.75rem'"
              :stullLabelsFontSize="'0.5rem'"
              :chartDivId="'umf-d3-chart-container'"
              :baseTypeId="searchQuery.params.base_type"
              :colortype="'r2o'"
              :showRecipes="true"
              :showCones="false"
              :showImages="isShowChartImages"
              :showStullChart="true"
              :showStullLabels="false"
              :showZoomButtons="false"
              :showAxesLabels="true"
              :highlightedRecipeId="highlightedMaterialId"
              :unHighlightedRecipeId="unHighlightedMaterialId"
              :xOxide="searchQuery.params.x"
              :yOxide="searchQuery.params.y"
              v-on:clickedUmfD3Recipe="clickedD3Chart"
          >
          </umf-d3-chart>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">

          <div v-if="hasResults && isMapExpanded" class="form-row">
            <div class="col-md-12">
              <b-form-checkbox id="isShowChartImagesCheckbox"
                               v-model="isShowChartImages"
                               plain>
                Show images
              </b-form-checkbox>
            </div>
          </div>

          <search-form
                  v-if="searchQuery"
                  :searchQuery="searchQuery"
                  :searchUser="searchUser"
                  v-on:searchrequest="search"
                  :isLarge="isMapExpanded">
          </search-form>
        </div>
      </div>
    </nav>

    <main v-bind:class="mainClass" role="main" class="ml-sm-auto search-results">

      <b-alert v-if="apiError" show variant="danger">
        API Error: {{ apiError.message }}
      </b-alert>
      <b-alert v-if="serverError" show variant="danger">
        Server Error: {{ serverError }}
      </b-alert>

      <div v-if="searchUser" class="row">
        <div class="col-sm-12 d-xl-none d-lg-none d-md-none">
          <user-profile-card 
              :searchUser="searchUser"></user-profile-card>
          <div class="form-group">
            <label for="collectionsSelectSelect">Recipes, Materials & Bookmarks:</label>
            <b-form-select
                    id="collectionsSelectSelect"
                    size="sm"
                    v-model="selectedSearchTypeOrCollection"
                    :options="collectionsSelect"
                    value-field="id"
                    text-field="name"
                    @input="selectSearchType">
              <template slot="first">
                <option value="recipes">{{ isViewingSelfSelectText }} Recipes</option>
                <option value="materials">{{ isViewingSelfSelectText }} Materials</option>
                <option value="analyses">{{ isViewingSelfSelectText }} Analyses</option>
                <option value="images">{{ isViewingSelfSelectText }} Images</option>
                <option value="null" disabled>{{ isViewingSelfSelectText }} Bookmarks:</option>
              </template>
            </b-form-select>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12 d-xl-none d-lg-none d-md-none">
          <search-form
                  v-if="searchQuery"
                  :searchQuery="searchQuery"
                  :searchUser="searchUser"
                  v-on:searchrequest="search"
                  :isLarge="false">
          </search-form>
        </div>
      </div>
      <div v-if="$auth.check()" class="d-flex flex-column flex-md-row flex-lg-row flex-xl-row">
        <div class="flex-grow-1">
          <search-breadcrumbs :searchQuery="searchQuery"
                              :searchUser="searchUser"
                              :isViewingSelf="isViewingSelf"
                              :isViewingSelfCollection="isViewingSelfCollection"
                              v-on:deleteCollectionRequest="confirmDeleteCollection"
          ></search-breadcrumbs>
        </div>
        <div class="ml-2 text-right">
          <b-button-group class="checkbox-buttons">
            <b-btn @click="selectAllMaterials()"
                v-if="!isAllMaterialsSelected"
                size="sm"
                class="btn btn-link btn-checkbox">
                <i class="fa fa-ok"></i> Select All
            </b-btn>
            <b-btn @click="deselectAllMaterials()"
                v-else
                size="sm"
                class="btn btn-link btn-checkbox">
                <i class="fa fa-times"></i> Deselect All
            </b-btn>
            <b-dropdown class="dropdown-checkbox m-0" 
                right
                size="sm"
                variant="link"
                text="With Selected">
                <b-dropdown-item @click="collectSelectedMaterialsSelect()">Bookmark</b-dropdown-item>
                <b-dropdown-item @click="editSelectedMaterialsSelect()">Calculate</b-dropdown-item>
                <b-dropdown-item @click="printSelectedMaterialsSelect(false)">Print</b-dropdown-item>
                <b-dropdown-item @click="printSelectedMaterialsSelect(true)">Print Simple</b-dropdown-item>
            </b-dropdown>
          </b-button-group>
        </div>
      </div>
      <div v-else class="row">
        <div class="col-12">
          <search-breadcrumbs :searchQuery="searchQuery"
                              :searchUser="searchUser"
                              :isViewingSelf="isViewingSelf"
                              :isViewingSelfCollection="isViewingSelfCollection"
                              v-on:deleteCollectionRequest="confirmDeleteCollection"
          ></search-breadcrumbs>
        </div>
      </div>

      <filter-paginator
              v-if="hasResults && hasPagination"
              :pagination="searchPagination"
              :view="searchQuery.params.view"
              :order="order"
              :itemTypeName="itemTypeName"
              :isCompact="isMapExpanded"
              v-on:pagerequest="pageRequest"
              v-on:orderrequest="orderRequest"
              v-on:viewrequest="viewRequest">
      </filter-paginator>


      <div class="row">
        <div
                class="alert alert-warning col-sm-12"
                role="alert"
                v-if="(!searchItems || searchItems.length <= 0) && !isProcessing">
          <div class="container">
            <div class="alert-icon">
              <i class="fa fa-bell"></i>
            </div>
            <strong>No recipes found.</strong>
            Please try a broader search, or reset the search form.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">
                                <i class="fa fa-remove"></i>
                            </span>
            </button>
          </div>
        </div>
      </div>

      <section class="row search-results-row" v-if="(searchQuery.params.view === 'cards')">
        <div v-bind:class="materialCardClass"
             v-for="(material, index) in searchItems">
          <material-card-thumb
                  :material="material"
                  :isViewingSelf="isViewingSelf"
                  :isViewingSelfCollection="isViewingSelfCollection"
                  :isSelected="selectedMaterials[material.id]"
                  v-on:highlightMaterial="highlightMaterial"
                  v-on:unhighlightMaterial="unhighlightMaterial"
                  v-on:copyMaterialRequest="copyMaterial"
                  v-on:deleteMaterialRequest="confirmDeleteMaterial"
                  v-on:collectMaterialRequest="collectMaterialSelect"
                  v-on:uncollectMaterialRequest="uncollectMaterial"
                  v-on:selectMaterialRequest="selectMaterial"
          ></material-card-thumb>
        </div>
      </section>
      <section class="row search-results-row" v-else-if="(searchQuery.params.view === 'details')">
        <div v-bind:class="detailCardClass"
             v-for="(material, index) in searchItems">
          <material-card-detail
                  :material="material"
                  :isViewingSelf="isViewingSelf"
                  :isViewingSelfCollection="isViewingSelfCollection"
                  :showCollapse="true"
                  v-on:highlightMaterial="highlightMaterial"
                  v-on:unhighlightMaterial="unhighlightMaterial"
                  v-on:copyMaterialRequest="copyMaterial"
                  v-on:deleteMaterialRequest="confirmDeleteMaterial"
                  v-on:collectMaterialRequest="collectMaterialSelect"
                  v-on:uncollectMaterialRequest="uncollectMaterial"
                  v-on:selectMaterialRequest="selectMaterial"
          ></material-card-detail>
        </div>
      </section>

      <filter-paginator
              v-if="hasResults && hasPagination"
              :pagination="searchPagination"
              :view="searchQuery.params.view"
              :order="order"
              :itemTypeName="itemTypeName"
              :isCompact="isMapExpanded"
              :showTotals="true"
              v-on:pagerequest="pageRequest"
              v-on:orderrequest="orderRequest"
              v-on:viewrequest="viewRequest">
      </filter-paginator>

      <AppFooter/>

    </main>

    <b-modal id="deleteConfirmModal"
             ref="deleteConfirmModal"
             title="Delete Recipe?"
             v-on:ok="deleteMaterial"
             ok-title="Delete Forever"
    >
      <p>Once deleted, you will not be able to retrieve this recipe!</p>
    </b-modal>


    <b-modal id="deleteCollectionConfirmModal"
             ref="deleteCollectionConfirmModal"
             title="Delete Bookmark Folder?"
             v-on:ok="deleteCollection"
             ok-title="Delete Forever"
    >
      <p>Once deleted, this bookmark folder will be gone forever!</p>
    </b-modal>

    <b-modal id="collectModal"
             ref="collectModal"
             title="Bookmark Recipe"
             v-on:ok="collectMaterials"
             ok-title="Add"
    >
      <b-form-group
              v-if="collections && collections.length > 0"
              id="groupBookmarkCollections"
              label="Bookmark in:"
              label-for="bookmarkInCollection"
      >
        <b-form-select id="bookmarkInCollection"
                        v-model="selectedCollectionId"
                       :options="collections"
                       text-field="name"
                       value-field="id">
          <template slot="first">
            <option :value="0">-- Select a bookmark folder --</option>
          </template>
        </b-form-select>
      </b-form-group>

      <b-form-group
              id="groupName"
              label="Create a New Bookmark Folder:"
              label-for="collectionName"
              :feedback="feedbackCollectionName"
              :state="stateCollectionName"
      >
        <b-form-input id="collectionName"
                      :state="stateCollectionName"
                      v-model.trim="newCollectionName"
                      placeholder="Bookmark Folder Name"></b-form-input>
      </b-form-group>

    </b-modal>

  </div>
</template>

<script>

  import UmfD3Chart from 'vue-d3-stull-charts/src/components/UmfD3Chart.vue'
  import MaterialCardThumb from '../components/glazy/search/MaterialCardThumb.vue'
  import MaterialCardDetail from '../components/glazy/search/MaterialCardDetail.vue'
  import SearchBreadcrumbs from '../components/glazy/search/SearchBreadcrumbs.vue'
  import SearchForm from '../components/glazy/search/SearchForm.vue'
  import SearchQuery from '../components/glazy/search/search-query'
  import FilterPaginator from '../components/glazy/search/FilterPaginator.vue'
  import UserProfileCard from '../components/glazy/search/UserProfileCard.vue'
  import AppFooter from './layout/AppFooter.vue'

  import Vue from 'vue'

  export default {
    name: 'Search',
    metaInfo () {
      return {
        title: this.title,
        meta: [
          {
            'vmid': "description",
            'property': 'description',
            'content': 'Glazy Search for Recipes'
          },
          {
            'property': 'og:description',
            'content': 'Glazy Search for Recipes'
          },
          {
            'property': 'og:title',
            'content': this.title
          },
          {
            'property': 'og:url',
            'content': GLAZY_APP_URL + this.$route.fullPath
          },
          {
            'property': 'og:image',
            'content': 'https://glazy.org/img/logoPromo.png'
          },
          {
            'property': 'og:image:width',
            'content': 400
          },
          {
            'property': 'og:image:height',
            'content': 400
          },
          {
            'property': 'twitter:description',
            'content': 'Glazy Search for Recipes'
          }
        ]
      }
    },
    components: {
      AppFooter,
      FilterPaginator,
      MaterialCardThumb,
      MaterialCardDetail,
      // MaterialCardRow,
      UmfD3Chart,
      SearchForm,
      SearchBreadcrumbs,
      UserProfileCard
    },
    props: {
      isembedded: {
        type: Boolean,
        default: false
      },

      userId: {
        type: Number,
        default: null
      },

      selectedCollection: {
        type: Object,
        default: null
      }
    },
    data() {
      return {
        title: 'Search',
        materials: null,
        searchQuery: null,
        isProcessingLocal: false,
        chartHeight: 200,
        chartWidth: 0,
        chartMargin: {
          left: 24,
          right: 10,
          top: 0,
          bottom: 12
        },
        isShowChartImages: false,
        isMapExpanded: false,
        expandButtonText: '<i class="fa fa-resize-full"></i>',
        expandbuttonTooltip: 'Show More Map',
        sidebarClass: 'col-md-3',
        mainClass: 'col-md-9',
        materialCardClass: 'col-lg-3 col-md-4 col-sm-6 col-6 search-col',
        detailCardClass: 'col-lg-4 col-md-6 col-sm-12 col-12 search-col',
        currentPage: null,
        isThumbnailView: true,
        highlightedMaterialId: {},
        unHighlightedMaterialId: {},
        selectedCollectionId: 0,
        newCollectionName: '',
        materialsToCollect: {},
        toDeleteMaterialId: 0,
        toDeleteCollectionId: 0,
        minSearchTextLength: 3,
        apiError: null,
        serverError: null,
        selectedSearchTypeOrCollection: null,
        selectedMaterials: {},
        isAllMaterialsSelected: false,
        timeout: null,
        isLoaded: false
      }
    },
    computed: {
      searchItems () {
        return this.$store.getters['search/searchItems']
      },

      searchPagination () {
        return this.$store.getters['search/searchPagination']
      },

      searchUser () {
        return this.$store.getters['search/searchUser']
      },

      isViewingSelf () {
        var user = this.$auth.user()
        if (user && this.searchUser && user.id === this.searchUser.id) {
          return true
        }
        return false
      },

      isViewingSelfCollection () {
        if (this.isViewingSelf && this.searchQuery.params.collection) {
          return true
        }
        return false
      },

      isProcessing() {
        return this.isProcessingLocal || this.$store.getters['search/isProcessing']
      },

      hasResults () {
        if (this.searchItems
          && this.searchItems.length > 0) {
          return true;
        }
        return false;
      },

      hasPagination () {
        if (this.searchPagination) {
          return true;
        }
        return false;
      },

      order () {
        return this.searchQuery.params.order;
      },

      collections () {
        var user = this.$auth.user()
        if (user && user.collections && user.collections.length > 0) {
          return user.collections
        }
        return null
      },

      feedbackCollectionName() {
        return this.newCollectionName.length > 0 ? 'Enter at least 3 characters' : 'Please enter a name';
      },

      stateCollectionName() {
        return this.newCollectionName.length > 2 ? 'valid' : 'invalid';
      },

      collectionsSelect () {
        // TODO: ensure only user-viewable collections are returned
        var collections = []
        if (this.searchUser && this.searchUser.collections &&
          this.searchUser.collections.length > 0) {
          this.searchUser.collections.forEach((collection) => {
            collections.push({
              id: collection.id,
              name: collection.name + ' (' + collection.materialCount + ')'
            })
          })
        }
        return collections
      },

      isPrimitiveSearch: function () {
        if (this.$route.name === 'materials' ||
          this.$route.name === 'user-materials') {
          return true
        }
        return false
      },

      isViewingSelfSelectText: function () {
        if (this.searchUser) {
          if (this.isViewingSelf) {
            return 'My'
          }
          return this.searchUser.name + '\'s'
        }
      },

      itemTypeName: function () {
        if (this.isPrimitiveSearch) {
          return 'materials'
        }
        if (this.$route.name === 'user-images') {
          return 'images'
        }
        return 'recipes'
      }

    },

    created() {
      this.searchQuery = new SearchQuery(this.$route.query);
      var isPrimitive = false;
      var isAnalysis = false;
      if (this.$route.name === 'materials' || this.$route.name === 'user-materials') {
        isPrimitive = true;
      }
      if (this.$route.name === 'analyses' || this.$route.name === 'user-analyses') {
        isAnalysis = true;
      }
      if (this.$route.params && this.$route.params.id) {
        this.searchQuery.params.u = this.$route.params.id
        if (this.$route.name === 'user-images') {
          this.searchQuery.params.images = 1
        }
      }

      this.$store.dispatch('search/search', {
        query: this.searchQuery, isPrimitive: isPrimitive, isAnalysis: isAnalysis
      })

      this.setSelectedSearchType(this.$route.name);

      // Clear out the selected materials
      this.selectedMaterials = {};
      this.isAllMaterialsSelected = false;
    },
    watch: {
      $route (route) {
        this.isLoaded = false;
        if (route.hash) {
          // This is only an internal link, no need to requery
          return
        }
        if (route.name !== 'search'
          && route.name !== 'user'
          && route.name !== 'user-materials'
          && route.name !== 'user-analyses'
          && route.name !== 'user-images'
          && route.name !== 'materials'
          && route.name !== 'analyses') {
          return; // we're not interested in non-search routes
        }

        this.searchQuery = new SearchQuery(route.query)
        var isPrimitive = false;
        var isAnalysis = false;
        if (route.name === 'materials' || route.name === 'user-materials') {
          isPrimitive = true;
        }
        if (route.name === 'analyses' || route.name === 'user-analyses') {
          isAnalysis = true;
        }
        if ('params' in route && route.params.id) {
          this.searchQuery.params.u = route.params.id
          if (route.name === 'user-images') {
            this.searchQuery.params.images = 1
          }
        }
        this.$store.dispatch('search/search', {
          query: this.searchQuery, isPrimitive: isPrimitive, isAnalysis: isAnalysis
        })
        this.setSelectedSearchType(route.name);

        // Clear out the selected materials
        this.selectedMaterials = {};
        this.isAllMaterialsSelected = false;
        this.isLoaded = true;
      }
    },
    mounted() {
      this.timeout = setTimeout(() => {
          this.handleResize()
        }, 2000)
      window.addEventListener('resize', this.handleResize)
    },
    beforeDestroy() {
      clearTimeout(this.timeout);
      window.removeEventListener('resize', this.handleResize);
    },
    methods: {

      newSearch () {
        var myParams = this.searchQuery.params;
        const myQuery = this.searchQuery.getMinimalQuery()
        if ('u' in myQuery) {
          delete myQuery.u // 'u' param is in the route
        }
        if ('images' in myQuery) {
          delete myQuery.images // 'images' param is in the route
        }
        // Update the route.  Actual search triggered in beforeRouteUpdate
        this.$router.push({path: this.$route.path, query: myQuery})
      },
      setSelectedSearchType (routeName) {
        if (routeName === 'user-materials') {
          this.selectedSearchTypeOrCollection = 'materials'
        }
        else if (routeName === 'user-analyses') {
          this.selectedSearchTypeOrCollection = 'analyses'
        }
        else if (routeName === 'user-images') {
          this.selectedSearchTypeOrCollection = 'images'
        }
        else if (routeName === 'user' &&
          (
            !('collection' in this.searchQuery.params) ||
            ('collection' in this.searchQuery.params && !this.searchQuery.params.collection)
          )
        ) {
          this.selectedSearchTypeOrCollection = 'recipes'
        }
        else if (routeName === 'user' &&
          'collection' in this.searchQuery.params &&
          this.searchQuery.params.collection) {
          // We're looking at a user collection
          this.selectedSearchTypeOrCollection = this.searchQuery.params.collection
        }
      },
      selectSearchType () {
        if (this.selectedSearchTypeOrCollection === 'recipes') {
            this.$router.push({name: 'user'})
        }
        else if (this.selectedSearchTypeOrCollection === 'materials') {
          if (this.$route.name !== 'user-materials') {
            // Requested user materials route, not already at user materials route
            this.$router.push({name: 'user-materials'})
          }
        }
        else if (this.selectedSearchTypeOrCollection === 'analyses') {
          if (this.$route.name !== 'user-analyses') {
            // Requested user analyses route, not already at user materials route
            this.$router.push({name: 'user-analyses'})
          }
        }
        else if (this.selectedSearchTypeOrCollection === 'images') {
          if (this.$route.name !== 'user-images') {
            // Requested user route, not already at user route
            this.$router.push({name: 'user-images'})
          }
        }
        else if (this.selectedSearchTypeOrCollection) {
          // Request a general collection
          this.$router.push({name: 'user', query: { collection: this.selectedSearchTypeOrCollection }})
        }
      },
      search (query) {
        this.searchQuery.setFromSearchForm(query.params)
        // New search, so reset the page number
        this.searchQuery.params.p = null
        this.$nextTick(function () {
          // TODO: Investigate why nextTick is needed here.
          this.newSearch()
        })
      },
      pageRequest (p) {
        this.searchQuery.params.p = p
        this.newSearch()
      },
      orderRequest (order) {
        this.searchQuery.params.order = order
        this.newSearch()
      },
      viewRequest (view) {
        this.searchQuery.params.view = view
      },
      toggleExpandMap () {
        if (this.isMapExpanded) {
          this.expandButtonText = '<i class="fa fa-resize-full"></i>'
          this.expandbuttonTooltip = 'Show More Map'
          this.sidebarClass = 'col-md-3'
          this.mainClass = 'col-md-9'
          this.materialCardClass = 'col-lg-3 col-md-4 col-sm-6 col-6 search-col',
          this.detailCardClass = 'col-lg-4 col-md-6 col-sm-12 col-12 search-col'
          this.chartHeight = 200
          this.isShowChartImages = false
        } else {
          this.expandButtonText = '<i class="fa fa-resize-small"></i>'
          this.expandbuttonTooltip = 'Show Less Map'
          this.sidebarClass = 'col-md-9'
          this.mainClass = 'col-md-3'
          this.materialCardClass = 'col-12 search-col'
          this.detailCardClass = 'col-12 search-col'
          this.chartHeight = 300
        }
        this.isMapExpanded = !this.isMapExpanded
        this.$root.$emit('bv::hide::tooltip')
        this.timeout = setTimeout(() => {
          this.handleResize()
        }, 300)
      },

      thumbnailView () {
        this.isThumbnailView = true
      },
      listView () {
        this.isThumbnailView = false
      },
      handleResize: function () {
        if (document.getElementById('umf-d3-chart-container')) {
          // this.chartHeight = document.getElementById('umf-d3-chart-container').clientHeight
          this.chartWidth = document.getElementById('umf-d3-chart-container').clientWidth
        }
      },
      clickedD3Chart (data) {
        this.$router.push({ path: this.$route.path + '#material-card-' + data.id, query: this.$route.query })
      },
      highlightMaterial: function (id) {
        // this.highlightedMaterialId = id
        this.highlightedMaterialId = { id: id }
      },
      unhighlightMaterial: function (id) {
        // this.highlightedMaterialId = 0
        this.unHighlightedMaterialId = { id: id }
      },

      collectMaterialSelect(id) {
        if (id) {
          this.materialsToCollect[id] = true;
          this.$refs.collectModal.show()
        }
      },

      collectSelectedMaterialsSelect() {
        var hasMaterial = false
        for (var materialId in this.selectedMaterials) {
          if (this.selectedMaterials.hasOwnProperty(materialId)) {
            this.materialsToCollect[materialId] = true
            hasMaterial = true
          }
        }
        if (hasMaterial) {
          this.$refs.collectModal.show()
        }
      },

      editSelectedMaterialsSelect() {
        var hasMaterial = false
        let materialsToEdit = [];
        for (var materialId in this.selectedMaterials) {
          if (this.selectedMaterials.hasOwnProperty(materialId)) {
            materialsToEdit.push(materialId);
          }
        }
        if (materialsToEdit.length) {
          this.$router.push({ name: 'calculator', query: { id: materialsToEdit }});
        }
      },

      printSelectedMaterialsSelect(isSimple) {
        var hasMaterial = false
        let materialsToPrint = [];
        for (var materialId in this.selectedMaterials) {
          if (this.selectedMaterials.hasOwnProperty(materialId)) {
            materialsToPrint.push(materialId);
          }
        }
        if (materialsToPrint.length) {
          this.$router.push({ name: 'print', query: { id: materialsToPrint, simple: isSimple }});
        }
      },

      collectMaterials() {
        if (!this.materialsToCollect || this.materialsToCollect.length == 0) {
          return
        }
        if (!this.selectedCollectionId && !this.newCollectionName) {
          return
        }
        this.isProcessingLocal = true
        var materialIds = []
        for (var materialId in this.materialsToCollect) {
          if (this.materialsToCollect.hasOwnProperty(materialId)) {
            materialIds.push(materialId)
          }
        }
        var form = {
          collectionName: this.newCollectionName,
          collectionId: this.selectedCollectionId,
          materialIds: materialIds
        }
        Vue.axios.post(Vue.axios.defaults.baseURL + '/collectionmaterials', form)
          .then((response) => {
          if (response.data.error) {
            this.apiError = response.data.error
            console.log(this.apiError)
            this.isProcessingLocal = false
          } else {
            this.isProcessingLocal = false
            this.$notify({
              message: 'Bookmarked.',
              type: 'success'
            });
            this.$store.dispatch('search/refresh')
            if (this.newCollectionName) {
              // Refresh user collections
              this.$auth.fetch({
                success(res) {
                  console.log('user id: ' + this.$auth.user().id)
                },
                error() {
                  console.log('error fetching user');
                }
              })
            }
          }
          this.newCollectionName = ''
          this.materialsToCollect = {}
        })
        .catch(response => {
          this.serverError = response
          this.isProcessingLocal = false
          this.newCollectionName = ''
          this.materialsToCollect = {}
        })
      },

      uncollectMaterial(materialId) {
        if (!materialId || !this.searchQuery.params.collection) {
          return
        }
        this.isProcessingLocal = true
        Vue.axios.get(Vue.axios.defaults.baseURL +
          '/collectionmaterials/delete/' +
          this.searchQuery.params.collection + '/'
          + materialId)
          .then((response) => {
          if (response.data.error) {
            this.apiError = response.data.error
            console.log(this.apiError)
            this.isProcessingLocal = false
          } else {
            this.isProcessingLocal = false
            this.$notify({
              message: 'Removed from Bookmark Folder ',
              type: 'success'
            });
            this.$store.dispatch('search/refresh')
          }
        })
        .catch(response => {
          this.serverError = response
          console.log(response)
          this.isProcessingLocal = false
        })
      },

      selectMaterial(materialId) {
        if (materialId in this.selectedMaterials) {
          delete this.selectedMaterials[materialId]
        }
        else {
          this.selectedMaterials[materialId] = true
        }
      },

      selectAllMaterials() {
        if (this.searchItems) {
          this.searchItems.forEach((material) => {
            this.selectedMaterials[material.id] = true
          })
          this.isAllMaterialsSelected = true
        }
      },

      deselectAllMaterials() {
        if (this.searchItems) {
          this.searchItems.forEach((material) => {
            delete this.selectedMaterials[material.id]
          })
          this.isAllMaterialsSelected = false
        }
      },

      copyMaterial: function (id) {
        let url = Vue.axios.defaults.baseURL + '/recipes/' + id + '/copy';
        if (this.isViewingSelf && this.searchQuery.params.collection) {
          url += '/' + this.searchQuery.params.collection;
        }

        if (!id) {
          return
        }
        this.isProcessingLocal = true
        Vue.axios.get(url).then((response) => {
          if (response.data.error) {
            this.apiError = response.data.error
            console.log(this.apiError)
            this.isProcessingLocal = false
          } else {
            this.isProcessingLocal = false
            var materialCopy = response.data.data;
            this.$notify({
              message: 'Copied ' + materialCopy.name + ' to your recipes.',
              type: 'success'
            });
            this.$store.dispatch('search/refresh')
          }
        })
        .catch(response => {
          this.serverError = response;
          this.isProcessingLocal = false
        })
      },

      confirmDeleteMaterial: function(id) {
        if (id) {
          this.toDeleteMaterialId = id
          this.$refs.deleteConfirmModal.show();
        }
      },

      confirmDeleteCollection: function(id) {
        if (id) {
          this.toDeleteCollectionId = id
          this.$refs.deleteCollectionConfirmModal.show();
        }
      },

      deleteMaterial: function() {
        if (this.toDeleteMaterialId) {
          Vue.axios.delete(Vue.axios.defaults.baseURL + '/recipes/' + this.toDeleteMaterialId)
            .then((response) => {
            if (response.data.error) {
              this.apiError = response.data.error
              console.log(this.apiError)
              this.isProcessingLocal = false
            } else {
              this.toDeleteMaterialId = 0
              this.isProcessingLocal = false
              this.$notify({
                message: 'Deleted from your recipes.',
                type: 'success'
              });
              this.$store.dispatch('search/refresh')
              // this.newSearch()
            }
          })
          .catch(response => {
            this.toDeleteMaterialId = 0
            this.serverError = response;
            this.isProcessingLocal = false
          })
        }
      },

      deleteCollection: function(id) {
        if (this.toDeleteCollectionId) {
          Vue.axios.delete(Vue.axios.defaults.baseURL + '/collections/' + this.toDeleteCollectionId)
            .then((response) => {
            if (response.data.error) {
              this.apiError = response.data.error
              console.log(this.apiError)
              this.isProcessingLocal = false
            } else {
              // Refresh user collections
              this.$auth.fetch({
                success(res) {},
                error() {
                  console.log('error fetching user');
                }
              })
              this.toDeleteMaterialId = 0
              this.isProcessingLocal = false
              this.$notify({
                message: 'Deleted the collection.',
                type: 'success'
              });
              // Update the route to default
              this.$router.push({path: this.$route.path})
            }
        })
        .catch(response => {
            this.toDeleteCollectionId = 0
          this.serverError = response;
          this.isProcessingLocal = false
        })
        }
      }

    }
  }
</script>

<style>

  .search-row {
    background-color: #dedede;
  }

  .search-results-row {
    padding-left: 5px;
    padding-right: 5px;
  }

  .search-col {
    padding-right: 10px;
    padding-left: 10px;
  }

  .sidebar {
    background-color: #efefef;
    position: fixed;
    top: 50px;
    bottom: 0;
    left: 0;
    z-index: 1000;
    padding: 0 10px 10px 10px;
    overflow-x: hidden;
    overflow-y: auto; /* Scrollable contents if viewport is shorter than content. */
  }

  .expand-button {
    position: absolute;
    top: 24px;
    right: -4px;
    z-index: 1001;
    padding: 5px;
  }

  #umf-d3-chart-container {
    /* need fix tip bug position: relative; */
    margin-top: 10px;
  }

  .chart-form {
    padding: 0 15px;
  }

  .search-results {
    background-color: #dedede;
    padding-top: 15px;
    padding-bottom: 30px;
  }

  .search-pagination {
    margin-bottom: 10px;
    margin-top: 5px;
  }

  .search-buttons {
    margin-bottom: 16px;
    margin-top: 5px;
  }

  .search-buttons .btn {
    margin-bottom: 0px;
    margin-top: 0px;
  }

  .btn-checkbox {
    margin: 0;
    padding: 0 5px;
  }

  .dropdown-checkbox .btn {
    margin: 0;
  }

  .material-detail-table {
    border-top-style: hidden;
    border-bottom-style: hidden;
  }

  .search-title {
    margin-top: 0;
    margin-bottom: 5px;
  }

  .d3-tip {
    font-size: .875rem;
    line-height: 1.125rem;
    font-weight: normal;
    padding: .75rem;
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    border-radius: 2px;
    z-index: 999999;
    position: relative;
  }

  .vc-chrome {
    width: 100% !important;
    border: 1px solid #ced4da !important;
    border-radius: 0.2rem !important;
    box-shadow: none !important;
  }

</style>