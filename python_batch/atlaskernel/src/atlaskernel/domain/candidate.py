from pydantic import BaseModel

class Candidate(BaseModel):
    value: str
    score: float