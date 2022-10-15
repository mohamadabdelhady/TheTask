<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="card m-auto mt-4">
            <form class="p-2">
                <div class="formBody">
                <p class="mt-2"><span>Main Category </span><span class="asterisk">*</span></p>
                <v-select class="style-chooser" :options="mainCategories" label="name" v-model="selectedMainCategory"></v-select>
                <p class="mt-2"><span>Sub Category </span><span class="asterisk">*</span></p>
                <v-select class="style-chooser" :options="subcategories" label="name" v-model="selectedSubCategory" ></v-select>
                <div v-for="(option,index) in subCatOptions" :key="index">
                    <div :id="'option-'+index">
                    <p class="mt-2"><span>{{option.name}}</span></p>
                    <v-select @option:selected="()=>showOtherOption(option,selectedOptions[index],index,0)" class="style-chooser" :options="option.options" label="name" v-model="selectedOptions[index]" ></v-select>
                    <input v-show="isVisable(option.options.find(item=>item.name=='other'))" class="vs__dropdown-toggle other" type="text" name="otherContent"  v-on:input="()=>getOtherValue(option)" placeholder="enter your option">
                    <div v-if="doesHaveChild(selectedOptions[index])" :key="childOptions">
                            <div v-for="(op,i) in childOptions[index]" class="childOptions">
                            <p class="mt-2"><span>{{op.name}}</span></p>
                            <v-select :clearable="false" class="style-chooser" :options="op.options" v-model="selectedChild[index][i]" label="name" @option:selected="()=>showOtherOption(op,selectedChild[index][i],index,i+1)" ></v-select>
                                <input v-show="isVisable(op.options.find(item=>item.name=='other'))" class="vs__dropdown-toggle other" type="text" name="otherContent"  v-on:input="()=>getOtherValue(op)" placeholder="enter your option">
                                <hr class="horLine" v-if="childOptions[index].length==i+1">
                        </div>
                    </div>
                   </div>
                </div>
                </div>
                <div class="formButton mt-2">
                    <!-- Button trigger modal -->
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        Submit
                    </button>
                </div>
            </form>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Options Table</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="MyTable">
                                <thead>
                                <tr>
                                    <th>options name</th>
                                    <th>values</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(item,index) in selectedOptions">
                                    <td>{{subCatOptions[index].name}}</td>
                                    <td v-if="item!=null&&selectedChild[index]!=null">{{getAllChildOptions(index)}}</td>
                                    <td v-else-if="item!=null&&item.name!='other'">{{item.name}}</td>
                                    <td v-else-if="item!=null&&item.name=='other'">{{item.content}}</td>
                                    <td v-else>Not selected</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: "form-component",
    data() {
        return {
            mainCategories: [],
            selectedMainCategory: null,
            selectedSubCategory: null,
            selectedChild: [],
            subcategories: [],
            subCatOptions: [],
            childOptions: [],
            selectedOptions: [],
            level:0,
        }
    },
    methods:
        {
            isVisable(x) {
                return x.show;
            },
            showOtherOption(option, selected, i, l = null) {
                let x = option.options.find(item => item.name == 'other');
                if (selected.name == 'other')
                    x.show = true;
                else
                    x.show = false;
                this.getOption(selected, i, l)

            },
            getOtherValue(option) {
                let val = event.target.value;
                let selector = option.options.find(item => item.name == 'other');
                selector.content = val;
            },
            doesHaveChild(selected) {
                if (selected != null)
                    return selected.child;
                else
                    return false
            },
            getOption(selected, i, l = null) {
                if (this.doesHaveChild(selected)) {
                    if (l < 0) {
                        this.childOptions[i] = [];
                        this.selectedChild[i] = [];
                    }

                    if (selected == null) {
                        this.selectedChild.pop();
                        this.childOptions.pop();
                    }
                    axios
                        .get('/api/getOptionChildren/' + selected.id)
                        .then(response => {
                            let r = response.data.data;
                            r.forEach((option, index) => {
                                option.options.push({name: "other", show: false, content: ""});

                            });
                            if (l > 0)
                                this.childOptions[i].push(r[0]);
                            else {
                                this.childOptions[i] = r;
                                let arr2 = [];
                                this.selectedChild[i] = arr2;
                            }
                        })
                        .catch(error => {
                            console.log(error)
                        });
                }
            },
            getAllChildOptions(i) {
                let o = this.selectedOptions[i].name;
                this.selectedChild[i].forEach(item=>{
                    if(item.name=='other')
                        o+='/'+item.content;
                    else
                        o+='/'+item.name;
                })
                return o;
            }
            },
            beforeMount: function () {
                this.mainCategories = [];
                axios
                    .get('/api/getAllCategories')
                    .then(response => {
                        this.mainCategories = response.data.data.categories;
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            watch: {
                selectedMainCategory: function (newVal) {
                    if (newVal != null) {
                        this.subcategories = [];
                        this.subcategories = newVal.children;
                        this.selectedSubCategory = null;
                        this.subCatOptions = [];
                        this.childOptions = [];
                        this.selectedOptions = [];
                    } else {
                        this.subcategories = [];
                        this.subCatOptions = [];
                        this.childOptions = [];
                        this.selectedOptions = [];
                        this.selectedSubCategory = null;
                    }
                },

                selectedSubCategory: function (newVal) {
                    if (newVal != null) {
                        this.subCatOptions = [];
                        this.childOptions = [];
                        this.selectedOptions = [];
                        axios
                            .post('/api/getCategoryOption', {selectedSubCat: this.selectedSubCategory.id})
                            .then(response => {
                                this.subCatOptions = response.data.data;
                                this.subCatOptions.forEach((option, index) => {
                                    option.options.push({name: "other", show: false, content: ""})
                                });
                            })
                            .catch(error => {
                                console.log(error)
                            });
                    } else {
                        this.subCatOptions = [];
                        this.childOptions = [];
                        this.selectedOptions = [];
                    }
                },
            }
}
</script>

<style scoped>
.asterisk{
    color:darkred;
}
.childOptions{
    margin-left: 2rem;
    margin-right: 2rem;
}
.other{
    margin: 1rem 0 1rem 0;
    width: 100%;
    height: 2rem;
    outline: none;
    /*display: none;*/
}
.formBody{
    overflow: auto;
    height: 30rem;
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.card
{
    width: 30rem;
}
.formBody::-webkit-scrollbar {
    display: none;
}
.horLine{
    opacity: 30%;
    color: grey;
}
 table.MyTable {
     width: 100%;
     background-color: #ffffff;
     border-collapse: collapse;
     border-width: 2px;
     border-color: #a3a3a3;
     border-style: solid;
     color: #000000;
 }

table.MyTable td, table.MyTable th {
    border-width: 2px;
    border-color: #a3a3a3;
    border-style: solid;
    padding: 3px;
}

table.MyTable thead {
    background-color: #fcfcfc;
}
</style>
