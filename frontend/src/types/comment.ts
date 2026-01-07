export type CommentUser = {
  id: number;
  name: string;
  user_image?: string | null;
};

export type Comment = {
  id: number;
  comment: string;
  created_at: string;
  user: CommentUser;
};
