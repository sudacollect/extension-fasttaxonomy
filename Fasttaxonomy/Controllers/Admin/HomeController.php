<?php

 
namespace App\Extensions\Fasttaxonomy\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Extensions\Fasttaxonomy\Controllers\AdminController;
use Gtd\Suda\Models\Setting;
use Gtd\Suda\Models\Taxonomy;
use Gtd\Suda\Models\Term;
use App\Extensions\Fasttaxonomy\Requests\TaxonomyRequest;

class HomeController extends AdminController{
    
    public $self_url = 'extension/fasttaxonomy/setting';
    
    public function index(Request $request,$param='')
    {
        
        $this->title('分类管理');
        
        //权限
        $this->gate('taxonomy_menu');
        //菜单
        $this->setMenu('taxonomy_menu');

        $page_no = $request->page?$request->page:1;
        $page_size = 20;



        $taxonomies = DB::table('Taxonomies')->select(DB::raw('`id`,`taxonomy`,`taxonomy_title`,`desc`,count(`taxonomy`) as count,`created_at`,`updated_at`'))
            ->where([])
            ->orderBy('created_at','desc')
            ->groupBy('taxonomy')
            ->paginate($page_size,['*'],'page',$page_no);

        $this->setData('data_list',$taxonomies);

        return $this->display('taxonomy.list');
    }

    public function showAddForm(Request $request)
    {
        $this->setData('modal_title','增加类型');
        return $this->display('taxonomy.add');
    }

    public function showEditForm(Request $request,$id)
    {
        $this->setData('modal_title','编辑类型');

        $data = Taxonomy::where(['id'=>$id])->with('term')->first();
        
        if(!$data)
        {
            return $this->responseAjax('fail','数据不存在');
        }

        $this->setData('data',$data);

        return $this->display('taxonomy.edit');
    }

    public function save(TaxonomyRequest $request)
    {
        //return $this->responseAjax('fail','数据不存在');

        if($request->id)
        {

            Taxonomy::where(['id'=>$request->id])->update([
                'taxonomy_title' => $request->taxonomy_title,
            ]);

            return $this->responseAjax('success','保存成功','self.refresh');

        }else{
            if(Taxonomy::where(['taxonomy'=>$request->taxonomy])->first())
            {
                return $this->responseAjax('fail','类型重复');
            }
            if(Term::where(['slug'=>$request->slug])->first())
            {
                return $this->responseAjax('fail','初始分类标识slug重复');
            }

            //创建term
            $term = new Term;
            $term->fill([
                'name' => $request->name,
                'slug' => $request->slug,
                'taxonomy' => $request->taxonomy
            ])->save();

            //创建taxonomy
            $taxonomy = new Taxonomy;
            $taxonomy->fill([
                'term_id'=>$term->id,
                'taxonomy'=>$request->taxonomy,
                'taxonomy_title'=>$request->taxonomy_title,
                'parent'=>0,
            ])->save();

            return $this->responseAjax('success','保存成功','self.refresh');
        }

    }

    public function delete(Request $request,$id)
    {
        if($id!=$request->id)
        {
            return $this->responseAjax('fail','数据不存在');
        }

        return $this->responseAjax('fail','类型数据无需删除。如需删除，删除类型下所有分类即可');
    }
    
    public function help(){
        
        $this->title('帮助');
        
        $this->setMenu('help_menu');

        return $this->display('help');
    }
    
    
}

