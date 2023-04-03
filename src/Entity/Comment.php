<?php

namespace App\Entity;

class Comment
{
    private int $commentId;
    private int $postId;
    private int $userId;
    private string $comment;
    private \DateTime $creationDate;
    private int $valid;


	/**
	 * @return int
	 */
	public function getCommentId(): int {
		return $this->commentId;
	}
	
	/**
	 * @param int $commentId 
	 */
	public function setCommentId(int $commentId) {
		$this->commentId = $commentId;
	}

	/**
	 * @return int
	 */
	public function getPostId(): int {
		return $this->postId;
	}
	
	/**
	 * @param int $postId 
	 */
	public function setPostId(int $postId) {
		$this->postId = $postId;
	}

	/**
	 * @return int
	 */
	public function getUserId(): int {
		return $this->userId;
	}
	
	/**
	 * @param int $userId 
	 */
	public function setUserId(int $userId) {
		$this->userId = $userId;
	}

	/**
	 * @return string
	 */
	public function getComment(): string {
		return $this->comment;
	}
	
	/**
	 * @param string $comment 
	 */
	public function setComment(string $comment) {
		$this->comment = $comment;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreationDate(): \DateTime {
		return $this->creationDate;
	}
	
	/**
	 * @param \DateTime $creationDate 
	 */
	public function setCreationDate(string $creationDate) {
		$this->creationDate = new \DateTime($creationDate);
	}

	/**
	 * @return int
	 */
	public function getValid(): int {
		return $this->valid;
	}
	
	/**
	 * @param int $valid 
	 */
	public function setValid(int $valid) {
		$this->valid = $valid;
	}
}