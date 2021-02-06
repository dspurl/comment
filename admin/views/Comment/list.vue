<template>
  <div class="app-container">
    <div class="filter-container">
      <el-form :inline="true" :model="listQuery" class="demo-form-inline">
        <el-form-item label="评价类型ID">
          <el-input v-model="listQuery.model_id" placeholder="评价类型ID" clearable @keyup.enter.native="handleFilter"/>
        </el-form-item>
        <el-form-item label="评价类型">
          <el-select v-model="listQuery.model_type" placeholder="类型" clearable>
            <el-option v-for="item in type" :key="item.value" :label="item.name" :value="item.value"/>
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleFilter">搜索</el-button>
        </el-form-item>
      </el-form>
    </div>

    <el-table
      v-loading="listLoading"
      ref="multipleTable"
      :key="tableKey"
      :data="list"
      border
      fit
      highlight-current-row
      style="width: 100%;"
      @sort-change="sortChange">
      <el-table-column label="编号" sortable="custom" prop="id" align="center" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="评价类型" sortable="custom" prop="model_type">
        <template slot-scope="scope">
          <div>{{ scope.row.model_type_show }}</div>
        </template>
      </el-table-column>
      <el-table-column label="类型ID" sortable="custom" prop="model_id">
        <template slot-scope="scope">
          <div>{{ scope.row.model_id }}</div>
        </template>
      </el-table-column>
      <el-table-column label="评价图片">
        <template slot-scope="scope">
          <el-image
            v-for="(item,index) of scope.row.resources_many"
            :key="index"
            :src="item.img"
            :preview-src-list="scope.row.resources_many.map((ite)=>{return ite.img})"
            style="width: 40px; height: 40px;margin: 2px;border-radius: 5px;"/>
        </template>
      </el-table-column>
      <el-table-column label="评价内容">
        <template slot-scope="scope">
          <div>{{ scope.row.details }}</div>
          <div v-if="scope.row.reply" style="color: #999999;">回复：{{ scope.row.reply.details }}</div>
        </template>
      </el-table-column>
      <el-table-column label="状态" align="center" width="80" sortable="custom" prop="state">
        <template slot-scope="scope">
          <span>{{ scope.row.state }}</span>
        </template>
      </el-table-column>
      <el-table-column label="创始时间" align="center" sortable="custom" prop="created_at">
        <template slot-scope="scope">
          <span>{{ scope.row.created_at }}</span>
        </template>
      </el-table-column>
      <el-table-column label="操作" class-name="small-padding fixed-width" width="120" fixed="right">
        <template slot-scope="scope">
          <el-tooltip class="item" effect="dark" content="审核" placement="top-start">
            <el-popover
              v-permission="$store.jurisdiction.CommentEdit"
              v-if="scope.row.state === '待审核'"
              placement="top"
              width="160">
              <p>审核是否通过？</p>
              <div style="text-align: right; margin: 0">
                <el-button size="mini" type="text" @click="setAudit(scope.row, 2)">不通过</el-button>
                <el-button type="primary" size="mini" @click="setAudit(scope.row, 1)">通过</el-button>
              </div>
              <el-button slot="reference" :loading="formLoading" type="warning" icon="el-icon-view" circle/>
            </el-popover>
          </el-tooltip>
          <el-tooltip v-permission="$store.jurisdiction.CommentEdit" v-if="!scope.row.reply" class="item" effect="dark" content="回复" placement="top-start">
            <el-button :loading="formLoading" type="primary" icon="el-icon-chat-line-square" circle @click="handleReply(scope.row)"/>
          </el-tooltip>
          <el-tooltip v-permission="$store.jurisdiction.CommentDestroy" class="item" effect="dark" content="删除" placement="top-start">
            <el-button :loading="formLoading" type="danger" icon="el-icon-delete" circle @click="handleDelete(scope.row)"/>
          </el-tooltip>
        </template>
      </el-table-column>
    </el-table>
    <!--回复-->
    <el-dialog
      :visible.sync="dialogVisible"
      title="回复"
      width="30%">
      <el-form ref="dataForm" :model="ruleForm" :rules="rules">
        <el-form-item prop="reply">
          <el-input
            :rows="2"
            v-model="ruleForm.reply"
            type="textarea"
            placeholder="请输入回复内容"/>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false">取 消</el-button>
        <el-button :loading="formLoading" type="primary" @click="setReply">确 定</el-button>
      </span>
    </el-dialog>
    <!--分页-->
    <div class="pagination-operation">
      <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" class="pagination" @pagination="getList"/>
    </div>
  </div>
</template>
<style rel="stylesheet/scss" lang="scss">
  .timeInterval{
    top:-4px;
  }
  .pagination-operation{
    margin-bottom: 80px;
    float:left;
  }
  .pagination-operation .operation{
    margin-left:20px;
    margin-top: 32px;
    font-size: 12px;
    float:left;
    margin-right: 10px;
  }
  .pagination-operation .pagination{
    float:left;
    padding: 0 0;
  }
  .drawing img{
    float:left;
  }
  .drawing .right{
    text-align: left;
    float:left;
    margin-left: 10px;
  }

  .avatar-uploader .el-upload {
    border: 1px dashed #d9d9d9;
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
  }
  .avatar-uploader .el-upload:hover {
    border-color: #409EFF;
  }
  .avatar-uploader-icon {
    font-size: 28px;
    color: #8c939d;
    width: 288px;
    height: 188px;
    line-height: 188px;
    text-align: center;
  }
  .progress-img{
    padding: 30px;
  }
  .avatar {
    width: 288px;
    height: 188px;
    display: block;
  }
</style>

<script>
import { getList, destroy, create, edit } from '@/api/comment'
import Pagination from '@/components/Pagination'

export default {
  name: 'CommentList',
  components: { Pagination },
  data() {
    return {
      formLoading: false,
      pickerOptions: {
        disabledDate(time) {
          return time.getTime() < Date.now() - 8.64e7
        }
      },
      tableKey: 0,
      list: null,
      total: 0,
      type: [
        {
          name: '商品',
          value: 'GoodIndentCommodity'
        }
      ],
      textMap: {
        update: '修改',
        create: '添加'
      },
      ruleForm: {
        reply: ''
      },
      dialogVisible: false,
      listLoading: false,
      dialogStatus: '',
      dialogFormVisible: false,
      listQuery: {
        page: 1,
        limit: 10,
        sort: '+id',
        model_id: '',
        model_type: ''
      },
      temp: {},
      rules: {
        reply: [
          { required: true, message: '请输入回复内容', trigger: 'blur' }
        ]
      }
    }
  },
  created() {
    this.getList()
  },
  methods: {
    getList() {
      this.listLoading = true
      getList(this.listQuery).then(response => {
        this.list = response.data.data
        this.total = response.data.total
        this.listLoading = false
      })
    },
    handleFilter() {
      this.getList()
    },
    sortChange(data) {
      const { prop, order } = data
      if (order === 'ascending') {
        this.listQuery.sort = '+' + prop
      } else {
        this.listQuery.sort = '-' + prop
      }
      this.handleFilter()
    },
    handleDelete(row) { // 删除
      const title = '删除后，评价和回复信息都将删除?'
      const win = '删除成功'
      this.$confirm(title, this.$t('hint.hint'), {
        confirmButtonText: this.$t('usuel.confirm'),
        cancelButtonText: this.$t('usuel.cancel'),
        type: 'warning'
      }).then(() => {
        this.formLoading = true
        destroy(row.id).then(() => {
          this.getList()
          this.dialogFormVisible = false
          this.formLoading = false
          this.$notify({
            title: this.$t('hint.succeed'),
            message: win,
            type: 'success',
            duration: 2000
          })
        }).catch(() => {
          this.formLoading = false
        })
      }).catch(() => {
      })
    },
    handleReply(row) { // 回复窗口
      this.ruleForm.parent_id = row.id
      this.ruleForm.model_type = row.model_type
      this.ruleForm.model_id = row.model_id
      this.dialogVisible = true
    },
    setReply() { // 回复
      this.formLoading = true
      this.$refs['dataForm'].validate((valid) => {
        if (valid) {
          this.dialogVisible = false
          create(this.ruleForm).then(() => {
            this.getList()
            this.dialogFormVisible = false
            this.formLoading = false
            this.$notify({
              title: this.$t('hint.succeed'),
              message: '回复成功',
              type: 'success',
              duration: 2000
            })
          }).catch(() => {
            this.formLoading = false
          })
        } else {
          this.formLoading = false
        }
      })
    },
    // 审核
    setAudit(row, result) {
      this.temp = row
      this.formLoading = true
      edit(result, this.temp).then(() => {
        this.getList()
        this.dialogFormVisible = false
        this.formLoading = false
        this.$notify({
          title: this.$t('hint.succeed'),
          message: '操作成功',
          type: 'success',
          duration: 2000
        })
      }).catch(() => {
        this.formLoading = false
      })
    }
  }
}
</script>
